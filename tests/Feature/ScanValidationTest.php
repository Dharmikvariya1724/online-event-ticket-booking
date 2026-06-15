<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Organiser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ScanValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.timezone' => 'Asia/Kolkata',
            'scout.driver' => null,
        ]);

        date_default_timezone_set('Asia/Kolkata');
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_guest_can_open_scan_page_in_viewer_mode(): void
    {
        $response = $this->get('/organiser/scan');

        $response->assertOk();
        $response->assertSee('Viewer Mode');
        $response->assertSee('Validation access disabled');
    }

    public function test_guest_can_open_ticket_qr_url_and_get_redirected_to_event_page(): void
    {
        $booking = $this->createBookingForEvent();
        $qrUrl = app(\App\Services\TicketQrCodeService::class)->payloadForBooking($booking);

        $response = $this->get($qrUrl);

        $response->assertRedirect(route('events.show', $booking->event->slug));
    }

    public function test_guest_cannot_validate_ticket_via_scan_api(): void
    {
        $booking = $this->createBookingForEvent();

        $response = $this->postJson('/api/scan-ticket', [
            'qr_code' => $booking->ticket_uuid,
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'type' => 'red',
                'code' => 'unauthenticated',
                'message' => 'Only organisers or admins can validate tickets.',
            ]);
    }

    public function test_organiser_can_validate_their_own_ticket(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 23, 18, 0, 0, 'Asia/Kolkata'));
        $booking = $this->createBookingForEvent();

        $response = $this
            ->withSession($this->organiserSession($booking->event->organiser))
            ->postJson('/api/scan-ticket', [
                'qr_code' => route('events.show', [
                    'slug' => $booking->event->slug,
                    'ticket_uuid' => $booking->ticket_uuid,
                    'booking_reference' => $booking->reference,
                ]),
            ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'type' => 'green',
                'code' => 'verified',
                'message' => 'Ticket Verified',
                'ticket_uuid' => $booking->ticket_uuid,
                'booking_reference' => $booking->reference,
            ]);

        $booking->refresh();
        $this->assertTrue($booking->is_used);
        $this->assertNotNull($booking->scanned_at);
    }

    public function test_organiser_can_validate_ticket_from_qr_redirect_url(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 23, 18, 5, 0, 'Asia/Kolkata'));
        $booking = $this->createBookingForEvent();
        $qrPayload = app(\App\Services\TicketQrCodeService::class)->payloadForBooking($booking);

        $response = $this
            ->withSession($this->organiserSession($booking->event->organiser))
            ->postJson('/api/scan-ticket', [
                'qr_code' => $qrPayload,
            ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'type' => 'green',
                'code' => 'verified',
                'ticket_uuid' => $booking->ticket_uuid,
                'booking_reference' => $booking->reference,
            ]);
    }

    public function test_organiser_can_validate_ticket_from_encoded_qr_payload(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 23, 18, 10, 0, 'Asia/Kolkata'));
        $booking = $this->createBookingForEvent();
        $qrPayload = app(\App\Services\TicketQrCodeService::class)->encodedPayloadForBooking($booking);

        $response = $this
            ->withSession($this->organiserSession($booking->event->organiser))
            ->postJson('/api/scan-ticket', [
                'qr_code' => $qrPayload,
            ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'type' => 'green',
                'code' => 'verified',
                'ticket_uuid' => $booking->ticket_uuid,
                'booking_reference' => $booking->reference,
            ]);
    }

    public function test_admin_can_validate_any_ticket(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 23, 18, 15, 0, 'Asia/Kolkata'));
        $booking = $this->createBookingForEvent();
        $admin = $this->createAdmin();

        $response = $this
            ->withSession($this->adminSession($admin))
            ->postJson('/api/scan-ticket', [
                'ticket_uuid' => $booking->ticket_uuid,
            ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'type' => 'green',
                'code' => 'verified',
                'message' => 'Ticket Verified',
            ]);
    }

    public function test_organiser_cannot_validate_another_organisers_ticket(): void
    {
        $booking = $this->createBookingForEvent();
        $otherOrganiser = $this->createOrganiser([
            'email' => 'other-organiser@example.com',
            'phone' => '07123456780',
        ]);

        $response = $this
            ->withSession($this->organiserSession($otherOrganiser))
            ->postJson('/api/scan-ticket', [
                'ticket_uuid' => $booking->ticket_uuid,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'type' => 'red',
                'code' => 'invalid_ticket',
                'message' => 'Invalid Organizer Ticket',
            ]);
    }

    public function test_it_returns_entry_not_started_before_validation_window(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 23, 10, 0, 0, 'Asia/Kolkata'));
        $booking = $this->createBookingForEvent([
            'ticket_validation_starts_at' => Carbon::now()->copy()->addHour(),
            'ticket_validation_ends_at' => Carbon::now()->copy()->addHours(6),
        ]);

        $response = $this
            ->withSession($this->organiserSession($booking->event->organiser))
            ->postJson('/api/scan-ticket', [
                'ticket_uuid' => $booking->ticket_uuid,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'type' => 'red',
                'code' => 'entry_not_started',
                'message' => 'Ticket Scanned Too Early',
            ]);
    }

    public function test_it_returns_entry_closed_after_validation_window(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 23, 23, 0, 0, 'Asia/Kolkata'));
        $booking = $this->createBookingForEvent([
            'ticket_validation_starts_at' => Carbon::now()->copy()->subHours(6),
            'ticket_validation_ends_at' => Carbon::now()->copy()->subHour(),
        ]);

        $response = $this
            ->withSession($this->organiserSession($booking->event->organiser))
            ->postJson('/api/scan-ticket', [
                'ticket_uuid' => $booking->ticket_uuid,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'type' => 'red',
                'code' => 'entry_closed',
                'message' => 'Ticket Expired',
            ]);
    }

    public function test_it_returns_cancelled_or_refunded_for_refunded_ticket(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 23, 18, 0, 0, 'Asia/Kolkata'));
        $booking = $this->createBookingForEvent();
        $booking->update(['status' => 'refunded']);

        $response = $this
            ->withSession($this->organiserSession($booking->event->organiser))
            ->postJson('/api/scan-ticket', [
                'ticket_uuid' => $booking->ticket_uuid,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'type' => 'red',
                'code' => 'cancelled_or_refunded',
                'message' => 'Ticket Not Valid',
            ]);
    }

    public function test_it_returns_already_used_when_ticket_was_scanned_before(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 23, 18, 0, 0, 'Asia/Kolkata'));
        $booking = $this->createBookingForEvent();
        $booking->update([
            'is_used' => true,
            'scanned_at' => Carbon::now()->subMinutes(15),
            'scanned_quantity' => 1,
        ]);

        $response = $this
            ->withSession($this->organiserSession($booking->event->organiser))
            ->postJson('/api/scan-ticket', [
                'ticket_uuid' => $booking->ticket_uuid,
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'status' => 'error',
                'type' => 'orange',
                'code' => 'already_used',
                'message' => 'Ticket Already Used',
            ]);
    }

    private function createBookingForEvent(array $eventOverrides = []): Booking
    {
        $organiser = $this->createOrganiser();

        $event = Event::create(array_merge([
            'organiser_id' => $organiser->id,
            'title' => 'Validation Test Event',
            'slug' => 'validation-test-event-' . uniqid(),
            'category' => 'Music',
            'starts_at' => Carbon::now()->copy()->addHours(2),
            'ends_at' => Carbon::now()->copy()->addHours(5),
            'ticket_validation_starts_at' => Carbon::now()->copy()->subHour(),
            'ticket_validation_ends_at' => Carbon::now()->copy()->addHours(3),
            'venue_name' => 'Test Venue',
            'venue_address' => 'Test Address',
            'city' => 'Ahmedabad',
            'country' => 'India',
            'status' => 'published',
            'approval_status' => 'approved',
            'approved_at' => Carbon::now()->copy()->subDay(),
        ], $eventOverrides));

        return Booking::create([
            'event_id' => $event->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '9999999999',
            'subtotal' => 100,
            'discount_amount' => 0,
            'total' => 100,
            'currency' => 'INR',
            'status' => 'paid',
        ]);
    }

    private function createOrganiser(array $overrides = []): Organiser
    {
        return Organiser::create(array_merge([
            'name' => 'Scan Organiser',
            'company_name' => 'Scan Organiser Co',
            'email' => 'scan-organiser@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ], $overrides));
    }

    private function createAdmin(array $overrides = []): Admin
    {
        return Admin::create(array_merge([
            'name' => 'Scan Admin',
            'email' => 'scan-admin@example.com',
            'password' => Hash::make('password123'),
        ], $overrides));
    }

    private function organiserSession(Organiser $organiser): array
    {
        return [
            'organiser_id' => $organiser->id,
            'organiser_last_active' => now()->timestamp,
        ];
    }

    private function adminSession(Admin $admin): array
    {
        return [
            'admin_id' => $admin->id,
            'admin_last_active' => now()->timestamp,
        ];
    }
}
