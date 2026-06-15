<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\PromoCode;
use App\Models\TicketTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrganiserOrderShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    public function test_organiser_order_show_displays_fee_percentages_and_soft_deleted_promo_label(): void
    {
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);
        $tier = $this->makeTicketTier($event);
        $promo = PromoCode::create([
            'organiser_id' => $organiser->id,
            'event_id' => $event->id,
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'reference' => 'TKT-ORGSHOW1',
            'event_id' => $event->id,
            'promo_code_id' => $promo->id,
            'customer_name' => 'Organiser Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '07123456789',
            'subtotal' => 100,
            'discount_amount' => 10,
            'portal_fee' => 10,
            'service_fee' => 5,
            'total' => 105,
            'currency' => 'GBP',
            'status' => 'paid',
        ]);

        BookingItem::create([
            'booking_id' => $booking->id,
            'ticket_tier_id' => $tier->id,
            'quantity' => 2,
            'unit_price' => 50,
            'subtotal' => 100,
        ]);

        $promo->delete();

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->get(route('organiser.orders.show', $booking->id));

        $response->assertOk();
        $response->assertSee('Portal Fee (10%)', false);
        $response->assertSee('Service Fee (5%)', false);
        $response->assertSee('Discount (SAVE10 - 10%)', false);
        $response->assertSee('-' . ticketly_money(10), false);
    }

    private function makeOrganiser(array $overrides = []): Organiser
    {
        return Organiser::create(array_merge([
            'name' => 'Orders Organiser',
            'company_name' => 'Orders Co',
            'email' => 'organiser.order.show@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ], $overrides));
    }

    private function makeEvent(Organiser $organiser, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'organiser_id' => $organiser->id,
            'title' => 'Organiser Order Show Event',
            'slug' => 'organiser-order-show-event',
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
            'price' => 50,
            'total_quantity' => 100,
            'available_quantity' => 98,
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
