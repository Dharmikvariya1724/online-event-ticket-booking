<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\PromoCode;
use App\Models\TicketTier;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminOrderPartialRefundHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->app->singleton(RefundService::class, fn () => new class extends RefundService
        {
            public function __construct()
            {
            }

            protected function createStripeRefund(array $refundData): object
            {
                static $refundCounter = 0;
                $refundCounter++;

                return (object) ['id' => 're_test_' . $refundCounter];
            }
        });
    }

    public function test_sequential_partial_refunds_update_remaining_totals_and_history(): void
    {
        $admin = $this->makeAdmin();
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);
        $tier = $this->makeTicketTier($event);

        $booking = Booking::create([
            'reference' => 'TKT-REFHIST1',
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
            'stripe_payment_intent_id' => 'pi_test_refund_history',
        ]);

        $item = BookingItem::create([
            'booking_id' => $booking->id,
            'ticket_tier_id' => $tier->id,
            'quantity' => 3,
            'unit_price' => 40,
            'subtotal' => 120,
        ]);

        $session = $this->adminSession($admin);

        $this->withSession($session)->post(route('admin.orders.partial-cancel', $booking->id), [
            'booking_item_id' => $item->id,
            'refund_quantity' => 1,
            'refund_reason' => 'Customer requested first ticket refund',
        ])->assertRedirect();

        $booking->refresh();
        $item->refresh();

        $this->assertSame('partially_refunded', $booking->status);
        $this->assertSame('46.00', $booking->refund_amount);
        $this->assertSame('80.00', $booking->subtotal);
        $this->assertSame('8.00', $booking->portal_fee);
        $this->assertSame('4.00', $booking->service_fee);
        $this->assertSame('92.00', $booking->total);
        $this->assertSame(2, $item->quantity);

        $this->withSession($session)->post(route('admin.orders.partial-cancel', $booking->id), [
            'booking_item_id' => $item->id,
            'refund_quantity' => 1,
            'refund_reason' => 'Customer requested second ticket refund',
        ])->assertRedirect();

        $booking->refresh();
        $item->refresh();
        $tier->refresh();

        $this->assertSame('partially_refunded', $booking->status);
        $this->assertSame('92.00', $booking->refund_amount);
        $this->assertSame('40.00', $booking->subtotal);
        $this->assertSame('4.00', $booking->portal_fee);
        $this->assertSame('2.00', $booking->service_fee);
        $this->assertSame('46.00', $booking->total);
        $this->assertSame(1, $item->quantity);
        $this->assertSame(99, $tier->available_quantity);

        $this->assertDatabaseCount('booking_refunds', 2);
        $this->assertDatabaseHas('booking_refunds', [
            'booking_id' => $booking->id,
            'stripe_refund_id' => 're_test_1',
            'original_total' => '138.00',
            'refunded_amount' => '46.00',
            'remaining_total' => '92.00',
            'reason' => 'Customer requested first ticket refund',
        ]);
        $this->assertDatabaseHas('booking_refunds', [
            'booking_id' => $booking->id,
            'stripe_refund_id' => 're_test_2',
            'original_total' => '92.00',
            'refunded_amount' => '46.00',
            'remaining_total' => '46.00',
            'reason' => 'Customer requested second ticket refund',
        ]);

        $response = $this
            ->withSession($session)
            ->get(route('admin.orders.show', $booking->id));

        $response->assertOk();
        $response->assertSee('Refunded Amount', false);
        $response->assertSee('Remaining Amount', false);
        $response->assertSee('Customer requested first ticket refund', false);
        $response->assertSee('Customer requested second ticket refund', false);
        $response->assertSee(ticketly_money(92), false);
        $response->assertSee(ticketly_money(46), false);
        $response->assertSeeInOrder([
            'Refund 1',
            ticketly_money(138),
            ticketly_money(46),
            ticketly_money(92),
            'Customer requested first ticket refund',
            'Refund 2',
            ticketly_money(92),
            ticketly_money(46),
            ticketly_money(46),
            'Customer requested second ticket refund',
        ], false);
    }

    public function test_partial_refund_with_fixed_promo_code_refunds_only_discounted_ticket_price(): void
    {
        $admin = $this->makeAdmin(['email' => 'refund.admin+promo@example.com']);
        $organiser = $this->makeOrganiser([
            'email' => 'refund.organiser+promo@example.com',
            'phone' => '07123456780',
        ]);
        $event = $this->makeEvent($organiser, ['slug' => 'refund-event-promo']);
        $tier = $this->makeTicketTier($event, ['available_quantity' => 97]);
        $promo = $this->makePromoCode($organiser, $event, ['code' => 'PROMO30']);

        $booking = Booking::create([
            'reference' => 'TKT-REFPROMO1',
            'event_id' => $event->id,
            'promo_code_id' => $promo->id,
            'customer_name' => 'Promo Customer',
            'customer_email' => 'promo.customer@example.com',
            'customer_phone' => '07123456789',
            'subtotal' => 120,
            'discount_amount' => 30,
            'portal_fee' => 12,
            'service_fee' => 6,
            'total' => 108,
            'currency' => 'GBP',
            'status' => 'paid',
            'stripe_payment_intent_id' => 'pi_test_refund_promo',
        ]);

        $item = BookingItem::create([
            'booking_id' => $booking->id,
            'ticket_tier_id' => $tier->id,
            'quantity' => 3,
            'unit_price' => 40,
            'subtotal' => 120,
        ]);

        $response = $this->withSession($this->adminSession($admin))->post(route('admin.orders.partial-cancel', $booking->id), [
            'booking_item_id' => $item->id,
            'refund_quantity' => 1,
            'refund_reason' => 'Promo booking refund',
        ]);

        $response->assertRedirect();

        $booking->refresh();
        $item->refresh();
        $tier->refresh();

        $this->assertSame('partially_refunded', $booking->status);
        $this->assertSame('36.00', $booking->refund_amount);
        $this->assertSame('80.00', $booking->subtotal);
        $this->assertSame('20.00', $booking->discount_amount);
        $this->assertSame('8.00', $booking->portal_fee);
        $this->assertSame('4.00', $booking->service_fee);
        $this->assertSame('72.00', $booking->total);
        $this->assertSame(2, $item->quantity);
        $this->assertSame(98, $tier->available_quantity);

        $this->assertDatabaseHas('booking_refunds', [
            'booking_id' => $booking->id,
            'original_total' => '108.00',
            'refunded_amount' => '36.00',
            'remaining_total' => '72.00',
            'reason' => 'Promo booking refund',
        ]);

        $this->withSession($this->adminSession($admin))->post(route('admin.orders.partial-cancel', $booking->id), [
            'booking_item_id' => $item->id,
            'refund_quantity' => 1,
            'refund_reason' => 'Promo booking refund second ticket',
        ])->assertRedirect();

        $booking->refresh();
        $item->refresh();
        $tier->refresh();

        $this->assertSame('partially_refunded', $booking->status);
        $this->assertSame('72.00', $booking->refund_amount);
        $this->assertSame('40.00', $booking->subtotal);
        $this->assertSame('10.00', $booking->discount_amount);
        $this->assertSame('4.00', $booking->portal_fee);
        $this->assertSame('2.00', $booking->service_fee);
        $this->assertSame('36.00', $booking->total);
        $this->assertSame(1, $item->quantity);
        $this->assertSame(99, $tier->available_quantity);

        $this->assertDatabaseHas('booking_refunds', [
            'booking_id' => $booking->id,
            'original_total' => '72.00',
            'refunded_amount' => '36.00',
            'remaining_total' => '36.00',
            'reason' => 'Promo booking refund second ticket',
        ]);
    }

    public function test_partial_refund_with_percentage_promo_code_refunds_only_discounted_ticket_price(): void
    {
        $admin = $this->makeAdmin(['email' => 'refund.admin+percentage@example.com']);
        $organiser = $this->makeOrganiser([
            'email' => 'refund.organiser+percentage@example.com',
            'phone' => '07123456770',
        ]);
        $event = $this->makeEvent($organiser, ['slug' => 'refund-event-percentage']);
        $tier = $this->makeTicketTier($event, ['available_quantity' => 97]);
        $promo = $this->makePromoCode($organiser, $event, [
            'code' => 'PROMO20P',
            'type' => 'percentage',
            'value' => 20,
        ]);

        $booking = Booking::create([
            'reference' => 'TKT-REFPERCENT1',
            'event_id' => $event->id,
            'promo_code_id' => $promo->id,
            'customer_name' => 'Percentage Customer',
            'customer_email' => 'percentage.customer@example.com',
            'customer_phone' => '07123456789',
            'subtotal' => 120,
            'discount_amount' => 27.60,
            'portal_fee' => 12,
            'service_fee' => 6,
            'total' => 110.40,
            'currency' => 'GBP',
            'status' => 'paid',
            'stripe_payment_intent_id' => 'pi_test_refund_percentage',
        ]);

        $item = BookingItem::create([
            'booking_id' => $booking->id,
            'ticket_tier_id' => $tier->id,
            'quantity' => 3,
            'unit_price' => 40,
            'subtotal' => 120,
        ]);

        $response = $this->withSession($this->adminSession($admin))->post(route('admin.orders.partial-cancel', $booking->id), [
            'booking_item_id' => $item->id,
            'refund_quantity' => 1,
            'refund_reason' => 'Percentage promo booking refund',
        ]);

        $response->assertRedirect();

        $booking->refresh();
        $item->refresh();
        $tier->refresh();

        $this->assertSame('partially_refunded', $booking->status);
        $this->assertSame('36.80', $booking->refund_amount);
        $this->assertSame('80.00', $booking->subtotal);
        $this->assertSame('18.40', $booking->discount_amount);
        $this->assertSame('8.00', $booking->portal_fee);
        $this->assertSame('4.00', $booking->service_fee);
        $this->assertSame('73.60', $booking->total);
        $this->assertSame(2, $item->quantity);
        $this->assertSame(98, $tier->available_quantity);

        $this->assertDatabaseHas('booking_refunds', [
            'booking_id' => $booking->id,
            'original_total' => '110.40',
            'refunded_amount' => '36.80',
            'remaining_total' => '73.60',
            'reason' => 'Percentage promo booking refund',
        ]);
    }

    public function test_partial_refund_with_promo_code_does_not_recalculate_fees_from_current_settings(): void
    {
        config([
            'ticketly.portal_fee_percentage' => 12.5,
            'ticketly.service_fee_percentage' => 7.5,
        ]);

        $admin = $this->makeAdmin(['email' => 'refund.admin+dynamicfees@example.com']);
        $organiser = $this->makeOrganiser([
            'email' => 'refund.organiser+dynamicfees@example.com',
            'phone' => '07123456760',
        ]);
        $event = $this->makeEvent($organiser, ['slug' => 'refund-event-dynamic-fees']);
        $tier = $this->makeTicketTier($event, ['available_quantity' => 97]);
        $promo = $this->makePromoCode($organiser, $event, ['code' => 'PROMODYNAMIC']);

        $booking = Booking::create([
            'reference' => 'TKT-REFDYNAMIC1',
            'event_id' => $event->id,
            'promo_code_id' => $promo->id,
            'customer_name' => 'Dynamic Fee Customer',
            'customer_email' => 'dynamic.fee.customer@example.com',
            'customer_phone' => '07123456789',
            'subtotal' => 120,
            'discount_amount' => 30,
            'portal_fee' => 15,
            'service_fee' => 9,
            'total' => 114,
            'currency' => 'GBP',
            'status' => 'paid',
            'stripe_payment_intent_id' => 'pi_test_refund_dynamic_fee',
        ]);

        $item = BookingItem::create([
            'booking_id' => $booking->id,
            'ticket_tier_id' => $tier->id,
            'quantity' => 3,
            'unit_price' => 40,
            'subtotal' => 120,
        ]);

        $this->withSession($this->adminSession($admin))->post(route('admin.orders.partial-cancel', $booking->id), [
            'booking_item_id' => $item->id,
            'refund_quantity' => 1,
            'refund_reason' => 'Dynamic fee promo refund',
        ])->assertRedirect();

        $booking->refresh();
        $item->refresh();

        $this->assertSame('80.00', $booking->subtotal);
        $this->assertSame('20.00', $booking->discount_amount);
        $this->assertSame('10.00', $booking->portal_fee);
        $this->assertSame('6.00', $booking->service_fee);
        $this->assertSame('76.00', $booking->total);
        $this->assertSame('38.00', $booking->refund_amount);
        $this->assertSame(2, $item->quantity);
    }

    private function makeAdmin(array $overrides = []): Admin
    {
        return Admin::create(array_merge([
            'name' => 'Refund Admin',
            'email' => 'refund.admin@example.com',
            'password' => Hash::make('password123'),
        ], $overrides));
    }

    private function makeOrganiser(array $overrides = []): Organiser
    {
        return Organiser::create(array_merge([
            'name' => 'Refund Organiser',
            'company_name' => 'Refund Co',
            'email' => 'refund.organiser@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ], $overrides));
    }

    private function makeEvent(Organiser $organiser, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'organiser_id' => $organiser->id,
            'title' => 'Refund Event',
            'slug' => 'refund-event',
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

    private function makePromoCode(Organiser $organiser, Event $event, array $overrides = []): PromoCode
    {
        return PromoCode::create(array_merge([
            'organiser_id' => $organiser->id,
            'event_id' => $event->id,
            'code' => 'PROMO10',
            'type' => 'fixed',
            'value' => 30,
            'max_discount' => null,
            'max_uses' => 50,
            'used_count' => 1,
            'is_active' => true,
            'expires_at' => now()->addDays(10),
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
