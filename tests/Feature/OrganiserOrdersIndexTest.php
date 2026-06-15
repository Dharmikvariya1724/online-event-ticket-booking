<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\TicketTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrganiserOrdersIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    public function test_organiser_orders_index_displays_total_tickets_for_each_order(): void
    {
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);
        $tier = $this->makeTicketTier($event);

        $booking = Booking::create([
            'reference' => 'TKT-ORG001',
            'event_id' => $event->id,
            'customer_name' => 'Organiser Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '07123456789',
            'subtotal' => 150,
            'discount_amount' => 0,
            'portal_fee' => 15,
            'service_fee' => 7.5,
            'total' => 172.5,
            'currency' => 'GBP',
            'status' => 'paid',
        ]);

        BookingItem::create([
            'booking_id' => $booking->id,
            'ticket_tier_id' => $tier->id,
            'quantity' => 4,
            'unit_price' => 37.5,
            'subtotal' => 150,
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->get(route('organiser.orders.index'));

        $response->assertOk();
        $response->assertSee('TKT-ORG001');
        $response->assertSee('4 tickets');
    }

    private function makeOrganiser(array $overrides = []): Organiser
    {
        return Organiser::create(array_merge([
            'name' => 'Orders Organiser',
            'company_name' => 'Orders Co',
            'email' => 'organiser.orders@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ], $overrides));
    }

    private function makeEvent(Organiser $organiser, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'organiser_id' => $organiser->id,
            'title' => 'Organiser Orders Event',
            'slug' => 'organiser-orders-event',
            'short_description' => 'Short description',
            'description' => 'Event description',
            'category' => 'Music',
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(4),
            'ticket_validation_starts_at' => now()->addDays(7)->subHour(),
            'ticket_validation_ends_at' => now()->addDays(7)->addHours(4),
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
        ], $overrides));
    }

    private function makeTicketTier(Event $event, array $overrides = []): TicketTier
    {
        return TicketTier::create(array_merge([
            'event_id' => $event->id,
            'name' => 'General Admission',
            'description' => 'Standard entry',
            'price' => 37.5,
            'total_quantity' => 100,
            'available_quantity' => 96,
            'min_per_order' => 1,
            'max_per_order' => 10,
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides));
    }

    private function organiserSession(Organiser $organiser): array
    {
        return [
            'organiser_id' => $organiser->id,
            'organiser_last_active' => now()->timestamp,
        ];
    }
}
