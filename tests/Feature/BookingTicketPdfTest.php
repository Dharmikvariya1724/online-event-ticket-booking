<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\TicketTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookingTicketPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_pdf_still_generates_when_customer_name_contains_an_apostrophe(): void
    {
        $this->assertTicketPdfGeneratesForCustomerName("Kevin O'Leary");
    }

    public function test_ticket_pdf_still_generates_when_customer_name_contains_a_dot(): void
    {
        $this->assertTicketPdfGeneratesForCustomerName('Charles Jr.');
    }

    private function assertTicketPdfGeneratesForCustomerName(string $customerName): void
    {
        $qrRequestUrl = null;

        Http::fake([
            'https://api.qrserver.com/*' => function ($request) use (&$qrRequestUrl) {
                $qrRequestUrl = $request->url();

                return Http::response('fake-qr-png-binary', 200, [
                    'Content-Type' => 'image/png',
                ]);
            },
        ]);

        $booking = $this->makePaidBooking($customerName);

        $response = $this->get(route('booking.ticket.pdf', $booking->reference));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertNotNull($qrRequestUrl);
        $this->assertStringNotContainsString(rawurlencode($booking->customer_name), $qrRequestUrl);

        parse_str((string) parse_url($qrRequestUrl, PHP_URL_QUERY), $query);
        $scanUrl = (string) ($query['data'] ?? '');
        $this->assertSame(route('events.show', $booking->event->slug), strtok($scanUrl, '?'));

        parse_str((string) parse_url($scanUrl, PHP_URL_QUERY), $scanQuery);
        $this->assertSame($booking->ticket_uuid, $scanQuery['ticket_uuid'] ?? null);
        $this->assertSame($booking->reference, $scanQuery['booking_reference'] ?? null);
    }

    private function makePaidBooking(string $customerName): Booking
    {
        $organiser = Organiser::create([
            'name' => 'PDF Organiser',
            'company_name' => 'PDF Co',
            'email' => 'pdf.organiser@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ]);

        $event = Event::create([
            'organiser_id' => $organiser->id,
            'title' => 'PDF Ticket Event',
            'slug' => 'pdf-ticket-event',
            'short_description' => 'Short description',
            'description' => 'Event description',
            'category' => 'Comedy',
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(5)->addHours(2),
            'ticket_validation_starts_at' => now()->addDays(5)->subHour(),
            'ticket_validation_ends_at' => now()->addDays(5)->addHours(2),
            'venue_name' => 'Main Hall',
            'venue_address' => '123 Event Street',
            'city' => 'London',
            'country' => 'GB',
            'postcode' => 'E1 6AN',
            'parking_info' => 'Parking nearby',
            'refund_policy' => 'Refunds available up to 48 hours before the event.',
            'status' => 'published',
            'approval_status' => 'approved',
            'total_capacity' => 100,
        ]);

        $ticketTier = TicketTier::create([
            'event_id' => $event->id,
            'name' => 'General Admission',
            'description' => 'Standard entry',
            'price' => 40,
            'total_quantity' => 100,
            'available_quantity' => 99,
            'min_per_order' => 1,
            'max_per_order' => 10,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $booking = Booking::create([
            'reference' => 'TKT-APOST01',
            'event_id' => $event->id,
            'customer_name' => $customerName,
            'customer_email' => 'conan@example.com',
            'customer_phone' => '07123456789',
            'subtotal' => 40,
            'discount_amount' => 0,
            'portal_fee' => 4,
            'service_fee' => 2,
            'total' => 46,
            'currency' => 'GBP',
            'status' => 'paid',
        ]);

        BookingItem::create([
            'booking_id' => $booking->id,
            'ticket_tier_id' => $ticketTier->id,
            'quantity' => 1,
            'unit_price' => 40,
            'subtotal' => 40,
        ]);

        return $booking;
    }
}
