<?php

namespace Tests\Feature;

use App\Models\Organiser;
use App\Services\StripeConnectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use RuntimeException;
use Stripe\Exception\PermissionException;
use Tests\TestCase;

class OrganiserStripeConnectTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrganiser(array $overrides = []): Organiser
    {
        return Organiser::create(array_merge([
            'name' => 'Stripe Organiser',
            'company_name' => 'Stripe Co',
            'email' => 'stripe.organiser@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ], $overrides));
    }

    private function organiserSession(Organiser $organiser): array
    {
        return [
            'organiser_id' => $organiser->id,
            'organiser_last_active' => now()->timestamp,
        ];
    }

    public function test_connect_redirects_to_stripe_onboarding_url(): void
    {
        $organiser = $this->makeOrganiser();
        $refreshUrl = route('organiser.stripe.refresh');
        $returnUrl = route('organiser.stripe.return');
        $onboardingUrl = 'https://connect.stripe.com/setup/s/acct_123';

        $this->mock(StripeConnectService::class, function (MockInterface $mock) use ($organiser, $refreshUrl, $returnUrl, $onboardingUrl) {
            $mock->shouldReceive('ensureExpressAccount')
                ->once()
                ->withArgs(fn (Organiser $resolvedOrganiser) => $resolvedOrganiser->is($organiser))
                ->andReturn('acct_123');

            $mock->shouldReceive('createOnboardingLink')
                ->once()
                ->with('acct_123', $refreshUrl, $returnUrl)
                ->andReturn($onboardingUrl);
        });

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->get(route('organiser.stripe.connect'));

        $response->assertRedirect($onboardingUrl);
    }

    public function test_connect_shows_actionable_message_when_platform_account_has_not_enabled_connect(): void
    {
        $organiser = $this->makeOrganiser();

        $this->mock(StripeConnectService::class, function (MockInterface $mock) {
            $mock->shouldReceive('ensureExpressAccount')
                ->once()
                ->andThrow(PermissionException::factory(
                    "You can only create new accounts if you've signed up for Connect, which you can do at https://dashboard.stripe.com/connect."
                ));
        });

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->from(route('organiser.payouts.index'))
            ->get(route('organiser.stripe.connect'));

        $response->assertRedirect(route('organiser.payouts.index'));
        $response->assertSessionHasErrors('stripe');

        $this->assertSame(
            'Stripe Connect is not enabled on this site yet. Please ask the site admin to enable Connect for the platform Stripe account.',
            session('errors')->get('stripe')[0]
        );
    }

    public function test_connect_shows_configuration_message_when_stripe_secret_is_missing(): void
    {
        $organiser = $this->makeOrganiser([
            'email' => 'missing.secret@gmail.com',
            'phone' => '07987654321',
        ]);

        $this->mock(StripeConnectService::class, function (MockInterface $mock) {
            $mock->shouldReceive('ensureExpressAccount')
                ->once()
                ->andThrow(new RuntimeException(StripeConnectService::MISSING_SECRET_MESSAGE));
        });

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->from(route('organiser.profile.show'))
            ->get(route('organiser.stripe.connect'));

        $response->assertRedirect(route('organiser.profile.show'));
        $response->assertSessionHasErrors('stripe');

        $this->assertSame(
            'Stripe payouts are not configured yet. Please contact the site admin.',
            session('errors')->get('stripe')[0]
        );
    }
}
