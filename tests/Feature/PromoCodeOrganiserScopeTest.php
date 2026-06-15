<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organiser;
use App\Models\PromoCode;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PromoCodeOrganiserScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    private function makeOrganiser(string $key): Organiser
    {
        static $phoneSeed = 0;
        $phoneSeed++;

        return Organiser::create([
            'name' => 'Promo Organiser ' . strtoupper($key),
            'company_name' => 'Promo Co ' . strtoupper($key),
            'email' => 'promo-' . $key . '@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07' . str_pad((string) $phoneSeed, 9, '0', STR_PAD_LEFT),
            'is_approved' => true,
        ]);
    }

    private function organiserSession(Organiser $organiser): array
    {
        return [
            'organiser_id' => $organiser->id,
            'organiser_last_active' => now()->timestamp,
        ];
    }

    private function makeEvent(Organiser $organiser, string $key): Event
    {
        return Event::create([
            'organiser_id' => $organiser->id,
            'title' => 'Promo Event ' . strtoupper($key),
            'slug' => 'promo-event-' . $key,
            'short_description' => 'Promo scope event',
            'description' => 'Promo scope event description',
            'category' => 'Music',
            'starts_at' => Carbon::parse('2026-07-20 18:00:00')->addDays(strlen($key)),
            'ends_at' => Carbon::parse('2026-07-20 22:00:00')->addDays(strlen($key)),
            'ticket_validation_starts_at' => Carbon::parse('2026-07-20 16:00:00')->addDays(strlen($key)),
            'ticket_validation_ends_at' => Carbon::parse('2026-07-20 22:00:00')->addDays(strlen($key)),
            'venue_name' => 'Promo Venue',
            'venue_address' => '123 Promo Street',
            'city' => 'Ahmedabad',
            'country' => 'India',
            'postcode' => '380001',
            'parking_info' => 'Parking available',
            'refund_policy' => 'Refunds allowed',
            'status' => 'published',
            'approval_status' => 'approved',
        ]);
    }

    private function makePromo(Organiser $organiser, string $code, ?Event $event = null, array $overrides = []): PromoCode
    {
        return PromoCode::create(array_merge([
            'organiser_id' => $organiser->id,
            'event_id' => $event?->id,
            'code' => strtoupper($code),
            'type' => 'percentage',
            'value' => 10,
            'max_discount' => null,
            'max_uses' => 50,
            'used_count' => 0,
            'is_active' => true,
            'expires_at' => now()->addDays(10),
        ], $overrides));
    }

    private function makeReservation(Event $event, string $token): Reservation
    {
        return Reservation::create([
            'token' => $token,
            'event_id' => $event->id,
            'session_id' => 'promo-scope-session-' . $token,
            'subtotal' => 100.00,
            'portal_fee' => 0,
            'service_fee' => 0,
            'discount_amount' => 0,
            'total' => 100.00,
            'expires_at' => now()->addMinutes(10),
            'status' => 'pending',
        ]);
    }

    private function promoPayload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
            'max_discount' => '',
            'max_uses' => 50,
            'expires_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'event_id' => '',
            'is_active' => 1,
        ], $overrides);
    }

    public function test_same_organiser_cannot_create_duplicate_promo_case_insensitively(): void
    {
        $organiser = $this->makeOrganiser('one');
        $this->makePromo($organiser, 'SAVE10');

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->post(route('organiser.promos.store'), $this->promoPayload(['code' => 'save10']));

        $response->assertSessionHasErrors([
            'code' => 'This promo code already exists for your account.',
        ]);

        $this->assertSame(1, PromoCode::withTrashed()->count());
    }

    public function test_different_organisers_can_create_the_same_promo_code(): void
    {
        $firstOrganiser = $this->makeOrganiser('alpha');
        $secondOrganiser = $this->makeOrganiser('beta');

        $this->makePromo($firstOrganiser, 'SAVE10');

        $response = $this
            ->withSession($this->organiserSession($secondOrganiser))
            ->post(route('organiser.promos.store'), $this->promoPayload(['code' => 'save10']));

        $response->assertRedirect(route('organiser.promos.index'));
        $this->assertDatabaseHas('promo_codes', [
            'organiser_id' => $secondOrganiser->id,
            'code' => 'SAVE10',
        ]);
        $this->assertSame(2, PromoCode::count());
    }

    public function test_public_promo_validation_rejects_other_organiser_code_for_event(): void
    {
        $eventOrganiser = $this->makeOrganiser('event');
        $promoOrganiser = $this->makeOrganiser('promo');
        $event = $this->makeEvent($eventOrganiser, 'validate');

        $this->makePromo($promoOrganiser, 'SAVE10');

        $response = $this->postJson(route('promo.validate'), [
            'code' => 'save10',
            'subtotal' => 100,
            'event_id' => $event->id,
        ]);

        $response->assertOk()->assertJson([
            'valid' => false,
            'message' => 'This promo code is not valid for this event.',
        ]);
    }

    public function test_public_promo_validation_returns_discount_based_on_total_before_discount(): void
    {
        config([
            'ticketly.portal_fee_percentage' => 10,
            'ticketly.service_fee_percentage' => 5,
        ]);

        $organiser = $this->makeOrganiser('pricing');
        $event = $this->makeEvent($organiser, 'pricing');
        $this->makePromo($organiser, 'SAVE20', $event, [
            'type' => 'percentage',
            'value' => 20,
        ]);

        $response = $this->postJson(route('promo.validate'), [
            'code' => 'SAVE20',
            'subtotal' => 100,
            'event_id' => $event->id,
        ]);

        $response->assertOk()->assertJson([
            'valid' => true,
            'gross_total' => 115.0,
            'discount' => 23.0,
            'message' => '20% discount applied - saving 23.00',
        ]);
    }

    public function test_checkout_intent_rejects_promo_code_from_different_organiser(): void
    {
        $eventOrganiser = $this->makeOrganiser('checkout');
        $promoOrganiser = $this->makeOrganiser('other');
        $event = $this->makeEvent($eventOrganiser, 'checkout');
        $reservation = $this->makeReservation($event, '744cbeba-5f6d-4716-aeb0-fb13c6b7859a');

        $this->makePromo($promoOrganiser, 'SAVE10');

        $response = $this->postJson(route('checkout.intent', $reservation->token), [
            'promo_code' => 'save10',
            'name' => 'Promo Customer',
            'email' => 'promo.customer@example.com',
            'phone' => '07123456789',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('promo_code');

        $this->assertSame('This promo code is not valid for this event.', $response->json('errors.promo_code.0'));
    }

    public function test_checkout_promo_endpoint_applies_discount_before_contact_details(): void
    {
        config([
            'ticketly.portal_fee_percentage' => 10,
            'ticketly.service_fee_percentage' => 5,
        ]);

        $organiser = $this->makeOrganiser('apply');
        $event = $this->makeEvent($organiser, 'apply');
        $promo = $this->makePromo($organiser, 'SAVE20', $event, [
            'type' => 'percentage',
            'value' => 20,
        ]);
        $reservation = $this->makeReservation($event, '4fe561d7-b34b-45a8-a44e-456af77561b5');

        $response = $this->postJson(route('checkout.promo', $reservation->token), [
            'promo_code' => 'save20',
        ]);

        $response->assertOk()->assertJson([
            'valid' => true,
            'code' => 'SAVE20',
            'gross_total' => 115.0,
            'discount' => 23.0,
            'amount' => 92.0,
            'message' => '20% discount applied - saving 23.00',
        ]);

        $reservation->refresh();

        $this->assertSame($promo->id, $reservation->promo_code_id);
        $this->assertSame('23.00', $reservation->discount_amount);
        $this->assertSame('10.00', $reservation->portal_fee);
        $this->assertSame('5.00', $reservation->service_fee);
        $this->assertSame('92.00', $reservation->total);
    }
}
