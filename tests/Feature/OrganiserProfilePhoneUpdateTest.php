<?php

namespace Tests\Feature;

use App\Models\Organiser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrganiserProfilePhoneUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    private function makeOrganiser(array $overrides = []): Organiser
    {
        return Organiser::create(array_merge([
            'name' => 'Profile Organiser',
            'company_name' => 'Profile Co',
            'email' => 'profile.organiser@gmail.com',
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

    public function test_profile_update_accepts_valid_11_digit_phone_starting_with_07(): void
    {
        $organiser = $this->makeOrganiser();

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->put('/organiser/profile', [
                'name' => 'Updated Name',
                'company_name' => 'Updated Company',
                'email' => 'updated.organiser@gmail.com',
                'phone' => '07987654321',
                'website' => 'https://example.com',
                'bio' => 'Updated bio',
            ]);

        $response->assertRedirect(route('organiser.profile.show'));

        $this->assertDatabaseHas('organisers', [
            'id' => $organiser->id,
            'phone' => '07987654321',
        ]);
    }

    public function test_profile_update_allows_keeping_the_current_phone_number(): void
    {
        $organiser = $this->makeOrganiser();

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->put('/organiser/profile', [
                'name' => 'Updated Name',
                'company_name' => 'Updated Company',
                'email' => 'updated.organiser@gmail.com',
                'phone' => '07123456789',
                'website' => 'https://example.com',
                'bio' => 'Updated bio',
            ]);

        $response->assertRedirect(route('organiser.profile.show'));
    }

    public function test_profile_update_rejects_phone_without_07_prefix(): void
    {
        $organiser = $this->makeOrganiser();

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->from('/organiser/profile/edit')
            ->put('/organiser/profile', [
                'name' => 'Updated Name',
                'company_name' => 'Updated Company',
                'email' => 'updated.organiser@gmail.com',
                'phone' => '08123456789',
                'website' => 'https://example.com',
                'bio' => 'Updated bio',
            ]);

        $response->assertSessionHasErrors('phone');
        $this->assertSame('Phone number must start with 07.', session('errors')->get('phone')[0]);
    }

    public function test_profile_update_rejects_phone_with_non_numeric_characters(): void
    {
        $organiser = $this->makeOrganiser();

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->from('/organiser/profile/edit')
            ->put('/organiser/profile', [
                'name' => 'Updated Name',
                'company_name' => 'Updated Company',
                'email' => 'updated.organiser@gmail.com',
                'phone' => '07abc123456',
                'website' => 'https://example.com',
                'bio' => 'Updated bio',
            ]);

        $response->assertSessionHasErrors('phone');
        $this->assertSame('Phone number must be exactly 11 digits and contain numbers only.', session('errors')->get('phone')[0]);
    }

    public function test_profile_update_blocks_duplicate_phone(): void
    {
        $organiser = $this->makeOrganiser();
        $this->makeOrganiser([
            'name' => 'Existing Organiser',
            'company_name' => 'Existing Co',
            'email' => 'existing.organiser@gmail.com',
            'phone' => '07987654321',
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->from('/organiser/profile/edit')
            ->put('/organiser/profile', [
                'name' => 'Updated Name',
                'company_name' => 'Updated Company',
                'email' => 'updated.organiser@gmail.com',
                'phone' => '07987654321',
                'website' => 'https://example.com',
                'bio' => 'Updated bio',
            ]);

        $response->assertSessionHasErrors('phone');
        $this->assertSame('This phone number is already registered.', session('errors')->get('phone')[0]);
    }
}
