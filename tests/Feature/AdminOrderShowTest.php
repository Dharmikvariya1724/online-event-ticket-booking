<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\PromoCode;
use App\Models\TicketTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminOrderShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    public function test_admin_order_show_displays_portal_and_service_fee_percentages(): void
    {
        $admin = $this->makeAdmin();
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);
        $tier = $this->makeTicketTier($event);

        $booking = Booking::create([
            'reference' => 'TKT-ORDER15',
            'event_id' => $event->id,
            'customer_name' => 'Jane Customer',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '07123456789',
            'subtotal' => 120,
            'discount_amount' => 0,
            'portal_fee' => 12,
            'service_fee' => 6,
            'total' => 138,
            'currency' => 'GBP',
            'status' => 'paid',
        ]);

        BookingItem::create([
            'booking_id' => $booking->id,
            'ticket_tier_id' => $tier->id,
            'quantity' => 3,
            'unit_price' => 40,
            'subtotal' => 120,
        ]);

        $response = $this
            ->withSession($this->adminSession($admin))
            ->get(route('admin.orders.show', $booking->id));

        $response->assertOk();
        $response->assertSee('Portal Fee (10%)', false);
        $response->assertSee('Service Fee (5%)', false);
    }

    public function test_admin_order_show_displays_soft_deleted_promo_code_details_in_discount_label(): void
    {
        $admin = $this->makeAdmin([
            'email' => 'orders.show.promo.admin@example.com',
        ]);
        $organiser = $this->makeOrganiser([
            'email' => 'orders.show.promo.organiser@example.com',
        ]);
        $event = $this->makeEvent($organiser, [
            'slug' => 'orders-event-show-promo',
        ]);
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
            'reference' => 'TKT-ORDER16',
            'event_id' => $event->id,
            'promo_code_id' => $promo->id,
            'customer_name' => 'John Promo',
            'customer_email' => 'john.promo@example.com',
            'customer_phone' => '07123456780',
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
            'quantity' => 1,
            'unit_price' => 100,
            'subtotal' => 100,
        ]);

        $promo->delete();

        $response = $this
            ->withSession($this->adminSession($admin))
            ->get(route('admin.orders.show', $booking->id));

        $response->assertOk();
        $response->assertSee('Discount (SAVE10 - 10%)', false);
        $response->assertSee('-' . ticketly_money(10), false);
    }

    private function makeAdmin(array $overrides = []): Admin
    {
        return Admin::create(array_merge([
            'name' => 'Orders Admin',
            'email' => 'orders.show.admin@example.com',
            'password' => Hash::make('password123'),
        ], $overrides));
    }

    private function makeOrganiser(array $overrides = []): Organiser
    {
        return Organiser::create(array_merge([
            'name' => 'Orders Organiser',
            'company_name' => 'Orders Co',
            'email' => 'orders.show.organiser@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ], $overrides));
    }

    private function makeEvent(Organiser $organiser, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'organiser_id' => $organiser->id,
            'title' => 'Orders Event',
            'slug' => 'orders-event-show',
            'short_description' => 'Short description',
            'description' => 'Event description',
            'category' => 'Music',
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(5)->addHours(3),
            'ticket_validation_starts_at' => now()->addDays(5)->subHour(),
            'ticket_validation_ends_at' => now()->addDays(5)->addHours(3),
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
            'price' => 40,
            'total_quantity' => 100,
            'available_quantity' => 97,
            'min_per_order' => 1,
            'max_per_order' => 10,
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides));
    }

    private function adminSession(Admin $admin): array
    {
        return [
            'admin_id' => $admin->id,
            'admin_last_active' => now()->timestamp,
        ];
    }
}
