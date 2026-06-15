<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organiser;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CheckoutPhoneValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    private function makeOrganiser(): Organiser
    {
        return Organiser::create([
            'name' => 'Checkout Organiser',
            'company_name' => 'Checkout Co',
            'email' => 'checkout.organiser@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ]);
    }

    private function makeEvent(Organiser $organiser): Event
    {
        return Event::create([
            'organiser_id' => $organiser->id,
            'title' => 'Checkout Validation Event',
            'slug' => 'checkout-validation-event',
            'short_description' => 'Checkout validation event',
            'description' => 'Checkout validation event description',
            'category' => 'Music',
            'starts_at' => Carbon::parse('2026-05-20 18:00:00'),
            'ends_at' => Carbon::parse('2026-05-20 22:00:00'),
            'ticket_validation_starts_at' => Carbon::parse('2026-05-20 16:00:00'),
            'ticket_validation_ends_at' => Carbon::parse('2026-05-20 22:00:00'),
            'venue_name' => 'Checkout Venue',
            'venue_address' => '123 Checkout Street',
            'city' => 'Ahmedabad',
            'country' => 'India',
            'postcode' => '380001',
            'parking_info' => 'Parking available',
            'refund_policy' => 'Refunds allowed',
            'status' => 'published',
            'approval_status' => 'approved',
        ]);
    }

    private function makeReservation(Event $event): Reservation
    {
        return Reservation::create([
            'token' => '2a6a4279-56d1-4aa0-beb8-5ccde4afbbda',
            'event_id' => $event->id,
            'session_id' => 'checkout-test-session',
            'subtotal' => 100.00,
            'portal_fee' => 0,
            'service_fee' => 0,
            'discount_amount' => 0,
            'total' => 100.00,
            'expires_at' => now()->addMinutes(10),
            'status' => 'pending',
        ]);
    }

    public function test_checkout_intent_rejects_phone_without_07_prefix(): void
    {
        $reservation = $this->makeReservation($this->makeEvent($this->makeOrganiser()));

        $response = $this->postJson('/checkout/' . $reservation->token . '/intent', [
            'name' => 'Checkout User',
            'email' => 'checkout.user@example.com',
            'phone' => '08123456789',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('phone');

        $this->assertSame('Phone number must start with 07', $response->json('errors.phone.0'));
    }

    public function test_checkout_intent_rejects_phone_with_non_numeric_characters(): void
    {
        $reservation = $this->makeReservation($this->makeEvent($this->makeOrganiser()));

        $response = $this->postJson('/checkout/' . $reservation->token . '/intent', [
            'name' => 'Checkout User',
            'email' => 'checkout.user@example.com',
            'phone' => '07abc123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('phone');

        $this->assertSame('Phone Number Must Be Exactly 11 digits', $response->json('errors.phone.0'));
    }

    public function test_checkout_intent_rejects_phone_with_spaces(): void
    {
        $reservation = $this->makeReservation($this->makeEvent($this->makeOrganiser()));

        $response = $this->postJson('/checkout/' . $reservation->token . '/intent', [
            'name' => 'Checkout User',
            'email' => 'checkout.user@example.com',
            'phone' => '07 123456789',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('phone');

        $this->assertSame('Phone Number Must Be Exactly 11 digits', $response->json('errors.phone.0'));
    }

    public function test_checkout_intent_requires_all_contact_fields(): void
    {
        $reservation = $this->makeReservation($this->makeEvent($this->makeOrganiser()));

        $response = $this->postJson('/checkout/' . $reservation->token . '/intent', [
            'promo_code' => 'SAVE10',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone']);

        $this->assertSame('Full name is required.', $response->json('errors.name.0'));
        $this->assertSame('Email address is required.', $response->json('errors.email.0'));
        $this->assertSame('Phone number is required.', $response->json('errors.phone.0'));
    }

    public function test_checkout_intent_rejects_full_name_longer_than_100_characters(): void
    {
        $reservation = $this->makeReservation($this->makeEvent($this->makeOrganiser()));

        $response = $this->postJson('/checkout/' . $reservation->token . '/intent', [
            'name' => str_repeat('A', 101),
            'email' => 'checkout.user@example.com',
            'phone' => '07123456789',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->assertSame('Full name may not be greater than 100 characters.', $response->json('errors.name.0'));
    }

    public function test_checkout_intent_rejects_full_name_with_invalid_special_characters(): void
    {
        $reservation = $this->makeReservation($this->makeEvent($this->makeOrganiser()));

        $response = $this->postJson('/checkout/' . $reservation->token . '/intent', [
            'name' => 'Guest!@#$%^&*()',
            'email' => 'checkout.user@example.com',
            'phone' => '07123456789',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->assertSame(
            'Full name may only contain letters, spaces, dots, and apostrophes.',
            $response->json('errors.name.0')
        );
    }

    public function test_checkout_intent_rejects_email_longer_than_100_characters(): void
    {
        $reservation = $this->makeReservation($this->makeEvent($this->makeOrganiser()));

        $response = $this->postJson('/checkout/' . $reservation->token . '/intent', [
            'name' => 'Checkout User',
            'email' => str_repeat('a', 89) . '@example.com',
            'phone' => '07123456789',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertSame('Email address may not be greater than 100 characters.', $response->json('errors.email.0'));
    }

    public function test_checkout_processing_page_contains_back_prevention_script(): void
    {
        $reservation = $this->makeReservation($this->makeEvent($this->makeOrganiser()));

        $response = $this->get('/checkout/' . $reservation->token . '/success');

        $response->assertOk();
        $response->assertSee('ticketly-global-loader', false);
        $response->assertSee('window.history.pushState', false);
        $response->assertSee('window.location.replace(data.redirect)', false);
        $response->assertSee('ticketly:checkout-complete-token', false);
    }

    public function test_checkout_page_renders_updated_contact_validation_markup(): void
    {
        $reservation = $this->makeReservation($this->makeEvent($this->makeOrganiser()));

        $response = $this->get('/checkout/' . $reservation->token);

        $response->assertOk();
        $response->assertSee('ticketly-global-loader', false);
        $response->assertSee('maxlength="100"', false);
        $response->assertSee('pattern="[A-Za-z .\']+"', false);
        $response->assertSee('Full name maximum limit reached.', false);
        $response->assertSee('Full name may only contain letters, spaces, dots, and apostrophes.', false);
        $response->assertSee('Email address maximum limit reached.', false);
        $response->assertSee('Phone number must start with 07', false);
        $response->assertSee('Phone Number Must Be Exactly 11 digits', false);
        $response->assertSee('window.location.replace(checkoutSuccessUrl);', false);
        $response->assertSee('ticketly:checkout-active-token', false);
    }
}
