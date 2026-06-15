<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingRefund;
use App\Models\Customer;
use App\Models\EmailLog;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\Payout;
use App\Models\PromoCode;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Sponsorship;
use App\Models\SystemSetting;
use App\Models\TicketTier;
use App\Models\User;
use App\Models\VisitorLog;
use App\Services\ServiceFeeCalculator;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class FakeProjectDataSeeder extends Seeder
{
    private const TARGET_COUNT = 50;
    private const SERVICE_FEE_RATE = 0.05;
    private const PORTAL_FEE_RATE = 0.10;

    private const CITY_VENUES = [
        'Ahmedabad' => ['Riverfront Arena', 'Science City Grounds', 'Sabarmati Expo Hall'],
        'Surat' => ['Diamond Convention Hub', 'Citylight Arena', 'Textile Square'],
        'Vadodara' => ['Lakshmi Hall', 'Palace Convention Center', 'Vadodara Arena'],
        'Rajkot' => ['Rajpath Club Hall', 'Heritage Expo Dome', 'Race Course Grounds'],
        'Mumbai' => ['BKC Convention Hall', 'Bandra Open Air', 'Jio Hall'],
        'Delhi' => ['Pragati Grounds', 'Saket Hall', 'Connaught Arena'],
        'Bengaluru' => ['Tech Park Center', 'Indiranagar Hall', 'Whitefield Arena'],
        'Pune' => ['Koregaon Park Pavilion', 'Pune Expo Center', 'Riverside Hall'],
    ];

    private const TIER_NAMES = [
        'General Admission',
        'VIP Pass',
        'Early Bird',
        'Premium Deck',
        'Student Pass',
        'Balcony Access',
        'Couple Entry',
        'Fan Zone',
    ];

    private const ROUTES = [
        ['name' => 'home', 'uri' => '/', 'path' => '/'],
        ['name' => 'events.index', 'uri' => 'events', 'path' => '/events'],
        ['name' => 'events.show', 'uri' => 'events/{slug}', 'path' => '/events/demo-event'],
        ['name' => 'checkout.show', 'uri' => 'checkout/{event}', 'path' => '/checkout/1'],
        ['name' => 'organiser.events.index', 'uri' => 'organiser/events', 'path' => '/organiser/events'],
        ['name' => 'admin.dashboard', 'uri' => 'admin', 'path' => '/admin'],
    ];

    public function run(): void
    {
        DB::disableQueryLog();

        $this->ensureOrganisersExist();
        $this->topUpUsers();
        $this->topUpPasswordResetTokens();
        $this->topUpSessions();
        $this->topUpCache();
        $this->topUpCacheLocks();
        $this->topUpJobs();
        $this->topUpJobBatches();
        $this->topUpFailedJobs();
        $this->topUpCustomers();
        $this->topUpEvents();
        $this->topUpTicketTiers();
        $this->syncEventCapacities();
        $this->topUpPromoCodes();
        $this->topUpReservations();
        $this->topUpReservationItems();
        $this->topUpBookings();
        $this->topUpBookingItems();
        $this->syncTicketInventory();
        $this->topUpSponsorships();
        $this->topUpPayouts();
        $this->topUpSystemSettings();
        $this->topUpEmailLogs();
        $this->topUpVisitorLogs();
        $this->topUpBookingRefunds();
        $this->syncTicketInventory();
        $this->syncEventCapacities();
        SystemSetting::flushCache();
    }

    private function ensureOrganisersExist(): void
    {
        if (DB::table('organisers')->count() > 0) {
            return;
        }

        $this->call(OrganiserSeeder::class);

        if (DB::table('organisers')->count() === 0) {
            throw new RuntimeException('FakeProjectDataSeeder requires at least one organiser record.');
        }
    }

    private function topUpUsers(): void
    {
        $remaining = $this->remaining('users');
        if ($remaining === 0) {
            return;
        }

        $rows = [];
        $start = DB::table('users')->count() + 1;

        for ($i = 0; $i < $remaining; $i++) {
            $index = $start + $i;
            $rows[] = [
                'name' => 'Demo User ' . $index,
                'email' => sprintf('demo-user-%03d@example.test', $index),
                'email_verified_at' => now()->subDays(random_int(0, 90)),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'created_at' => now()->subDays(random_int(0, 120)),
                'updated_at' => now(),
            ];
        }

        DB::table('users')->insert($rows);
    }

    private function topUpPasswordResetTokens(): void
    {
        $remaining = $this->remaining('password_reset_tokens');
        if ($remaining === 0) {
            return;
        }

        $users = User::query()->pluck('email')->all();
        if (empty($users)) {
            return;
        }

        $rows = [];
        $existingEmails = DB::table('password_reset_tokens')->pluck('email')->all();
        $availableEmails = array_values(array_diff($users, $existingEmails));

        while ($remaining > 0 && !empty($availableEmails)) {
            $email = array_shift($availableEmails);
            $rows[] = [
                'email' => $email,
                'token' => Hash::make(Str::random(40)),
                'created_at' => now()->subMinutes(random_int(1, 1440)),
            ];
            $remaining--;
        }

        if (!empty($rows)) {
            DB::table('password_reset_tokens')->insert($rows);
        }
    }

    private function topUpSessions(): void
    {
        $remaining = $this->remaining('sessions');
        if ($remaining === 0) {
            return;
        }

        $userIds = User::query()->pluck('id')->all();
        $rows = [];

        for ($i = 0; $i < $remaining; $i++) {
            $rows[] = [
                'id' => Str::random(40),
                'user_id' => !empty($userIds) && fake()->boolean(65) ? fake()->randomElement($userIds) : null,
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'payload' => base64_encode(json_encode([
                    'flash' => [],
                    'seeded' => true,
                    'issued_at' => now()->toIso8601String(),
                ])),
                'last_activity' => now()->subMinutes(random_int(1, 4320))->timestamp,
            ];
        }

        DB::table('sessions')->insert($rows);
    }

    private function topUpCache(): void
    {
        $remaining = $this->remaining('cache');
        if ($remaining === 0) {
            return;
        }

        $rows = [];

        for ($i = 0; $i < $remaining; $i++) {
            $rows[] = [
                'key' => 'seeded_cache_' . Str::lower(Str::random(18)),
                'value' => serialize([
                    'seeded' => true,
                    'label' => fake()->words(3, true),
                ]),
                'expiration' => now()->addDays(random_int(1, 30))->timestamp,
            ];
        }

        DB::table('cache')->insert($rows);
    }

    private function topUpCacheLocks(): void
    {
        $remaining = $this->remaining('cache_locks');
        if ($remaining === 0) {
            return;
        }

        $rows = [];

        for ($i = 0; $i < $remaining; $i++) {
            $rows[] = [
                'key' => 'seeded_lock_' . Str::lower(Str::random(18)),
                'owner' => Str::uuid()->toString(),
                'expiration' => now()->addHours(random_int(1, 48))->timestamp,
            ];
        }

        DB::table('cache_locks')->insert($rows);
    }

    private function topUpJobs(): void
    {
        $remaining = $this->remaining('jobs');
        if ($remaining === 0) {
            return;
        }

        $rows = [];
        $availableAt = now()->addYears(10)->timestamp;

        for ($i = 0; $i < $remaining; $i++) {
            $rows[] = [
                'queue' => 'sandbox_fake',
                'payload' => json_encode([
                    'uuid' => (string) Str::uuid(),
                    'displayName' => 'FakeSeededJob',
                    'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                    'maxTries' => null,
                    'timeout' => null,
                    'data' => [
                        'commandName' => 'FakeSeededJob',
                        'command' => 'O:13:"FakeSeededJob":0:{}',
                    ],
                ]),
                'attempts' => 0,
                'reserved_at' => null,
                'available_at' => $availableAt,
                'created_at' => now()->timestamp,
            ];
        }

        DB::table('jobs')->insert($rows);
    }

    private function topUpJobBatches(): void
    {
        $remaining = $this->remaining('job_batches');
        if ($remaining === 0) {
            return;
        }

        $rows = [];

        for ($i = 0; $i < $remaining; $i++) {
            $totalJobs = random_int(1, 20);
            $failedJobs = random_int(0, min(3, $totalJobs));
            $pendingJobs = random_int(0, $totalJobs - $failedJobs);

            $rows[] = [
                'id' => (string) Str::uuid(),
                'name' => 'Seeded Batch ' . ($i + 1),
                'total_jobs' => $totalJobs,
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
                'failed_job_ids' => json_encode([]),
                'options' => json_encode(['seeded' => true]),
                'cancelled_at' => null,
                'created_at' => now()->subDays(random_int(0, 60))->timestamp,
                'finished_at' => $pendingJobs === 0 ? now()->subDays(random_int(0, 30))->timestamp : null,
            ];
        }

        DB::table('job_batches')->insert($rows);
    }

    private function topUpFailedJobs(): void
    {
        $remaining = $this->remaining('failed_jobs');
        if ($remaining === 0) {
            return;
        }

        $rows = [];

        for ($i = 0; $i < $remaining; $i++) {
            $rows[] = [
                'uuid' => (string) Str::uuid(),
                'connection' => 'database',
                'queue' => 'sandbox_fake',
                'payload' => json_encode([
                    'seeded' => true,
                    'displayName' => 'FakeFailedJob',
                ]),
                'exception' => 'FakeSeededException: this row was generated by FakeProjectDataSeeder',
                'failed_at' => now()->subDays(random_int(0, 90)),
            ];
        }

        DB::table('failed_jobs')->insert($rows);
    }

    private function topUpCustomers(): void
    {
        $remaining = $this->remaining('customers');
        if ($remaining === 0) {
            return;
        }

        $start = DB::table('customers')->count() + 1;

        for ($i = 0; $i < $remaining; $i++) {
            $index = $start + $i;
            Customer::query()->create([
                'name' => fake()->name(),
                'email' => sprintf('customer-%03d@example.test', $index),
                'phone' => $this->fakePhone(),
                'is_suspended' => false,
                'notes' => fake()->optional(0.35)->sentence(),
            ]);
        }
    }

    private function topUpEvents(): void
    {
        $remaining = $this->remaining('events');
        if ($remaining === 0) {
            return;
        }

        $organisers = Organiser::query()->get();

        for ($i = 0; $i < $remaining; $i++) {
            $organiser = $organisers->random();
            $city = array_rand(self::CITY_VENUES);
            $venue = fake()->randomElement(self::CITY_VENUES[$city]);
            $title = fake()->unique()->sentence(3);
            $startsAt = $this->randomEventStart();
            $endsAt = (clone $startsAt)->addHours(random_int(2, 8));
            $approvalStatus = $this->randomWeightedValue([
                'approved' => 72,
                'pending' => 18,
                'rejected' => 10,
            ]);

            $event = Event::query()->create([
                'organiser_id' => $organiser->id,
                'title' => Str::title($title),
                'short_description' => fake()->sentence(12),
                'description' => fake()->paragraphs(3, true),
                'banner' => null,
                'category' => fake()->randomElement(Event::CATEGORIES),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'ticket_validation_starts_at' => (clone $startsAt)->subHours(2),
                'ticket_validation_ends_at' => (clone $endsAt)->addHour(),
                'venue_name' => $venue,
                'venue_address' => fake()->streetAddress(),
                'city' => $city,
                'country' => 'India',
                'postcode' => strtoupper(fake()->bothify('######')),
                'parking_info' => fake()->boolean(70) ? fake()->sentence() : null,
                'performer_lineup' => [
                    fake()->name(),
                    fake()->name(),
                    fake()->name(),
                ],
                'refund_policy' => fake()->sentence(16),
                'status' => $this->eventStatusFor($startsAt),
                'cancelled_at' => null,
                'cancellation_reason' => null,
                'is_featured' => fake()->boolean(25),
                'total_capacity' => 0,
                'approval_status' => $approvalStatus,
                'approved_at' => $approvalStatus === 'approved' ? now()->subDays(random_int(1, 45)) : null,
                'rejected_at' => $approvalStatus === 'rejected' ? now()->subDays(random_int(1, 30)) : null,
                'rejection_reason' => $approvalStatus === 'rejected' ? fake()->sentence() : null,
                'approved_by_admin_id' => null,
                'rejected_by_admin_id' => null,
            ]);

            if ($event->status === 'cancelled') {
                $event->update([
                    'cancelled_at' => now()->subDays(random_int(1, 10)),
                    'cancellation_reason' => fake()->sentence(),
                ]);
            }

            $tierCount = random_int(2, 3);
            for ($tierIndex = 0; $tierIndex < $tierCount; $tierIndex++) {
                $this->createTierForEvent($event, $tierIndex);
            }

            $this->refreshEventCapacity($event);
        }
    }

    private function topUpTicketTiers(): void
    {
        $remaining = $this->remaining('ticket_tiers');
        if ($remaining === 0) {
            return;
        }

        $events = Event::query()->get();
        if ($events->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $remaining; $i++) {
            $event = $events->random();
            $sortOrder = (int) $event->ticketTiers()->max('sort_order') + 1;
            $this->createTierForEvent($event, $sortOrder);
            $this->refreshEventCapacity($event);
        }
    }

    private function topUpPromoCodes(): void
    {
        $remaining = $this->remaining('promo_codes');
        if ($remaining === 0) {
            return;
        }

        $organisers = Organiser::query()->get();
        $events = Event::query()->get()->groupBy('organiser_id');
        $start = DB::table('promo_codes')->count() + 1;

        for ($i = 0; $i < $remaining; $i++) {
            $index = $start + $i;
            $organiser = $organisers->random();
            $organiserEvents = $events->get($organiser->id, collect());
            $type = fake()->randomElement(['percentage', 'fixed']);
            $maxUses = random_int(25, 250);

            PromoCode::query()->create([
                'organiser_id' => $organiser->id,
                'event_id' => $organiserEvents->isNotEmpty() && fake()->boolean(65)
                    ? $organiserEvents->random()->id
                    : null,
                'code' => 'DEMO' . str_pad((string) $index, 4, '0', STR_PAD_LEFT) . Str::upper(Str::random(2)),
                'type' => $type,
                'value' => $type === 'percentage' ? random_int(5, 30) : random_int(50, 500),
                'max_discount' => $type === 'percentage' ? random_int(100, 1200) : null,
                'max_uses' => $maxUses,
                'used_count' => random_int(0, min(20, $maxUses)),
                'is_active' => fake()->boolean(85),
                'expires_at' => now()->addDays(random_int(7, 180)),
            ]);
        }
    }

    private function topUpReservations(): void
    {
        $remaining = $this->remaining('reservations');
        if ($remaining === 0) {
            return;
        }

        $events = Event::query()->with('ticketTiers')->get()->filter(
            fn (Event $event) => $event->ticketTiers->isNotEmpty()
        )->values();
        $customers = Customer::query()->get()->values();
        $promoCodes = PromoCode::query()->get()->values();

        if ($events->isEmpty() || $customers->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $remaining; $i++) {
            $event = $events->random();
            $customer = $customers->random();
            $selectedTiers = $event->ticketTiers->shuffle()->take(random_int(1, min(3, $event->ticketTiers->count())));
            $promoCode = $this->pickPromoForEvent($promoCodes, $event);
            $status = $this->randomWeightedValue([
                'pending' => 35,
                'completed' => 35,
                'released' => 15,
                'expired' => 15,
            ]);

            $reservation = Reservation::query()->create([
                'token' => (string) Str::uuid(),
                'event_id' => $event->id,
                'session_id' => 'sess_demo_' . Str::lower(Str::random(24)),
                'customer_email' => $customer->email,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'promo_code_id' => $promoCode?->id,
                'discount_amount' => 0,
                'subtotal' => 0,
                'portal_fee' => 0,
                'service_fee' => 0,
                'total' => 0,
                'stripe_payment_intent_id' => in_array($status, ['completed', 'released'], true)
                    ? 'pi_demo_' . Str::lower(Str::random(24))
                    : null,
                'expires_at' => $status === 'pending'
                    ? now()->addMinutes(random_int(15, 180))
                    : now()->subMinutes(random_int(10, 1440)),
                'status' => $status,
            ]);

            foreach ($selectedTiers as $tier) {
                $quantity = random_int(1, min(4, max(1, (int) $tier->max_per_order)));
                ReservationItem::query()->create([
                    'reservation_id' => $reservation->id,
                    'ticket_tier_id' => $tier->id,
                    'quantity' => $quantity,
                    'unit_price' => $tier->price,
                    'subtotal' => round($quantity * (float) $tier->price, 2),
                ]);
            }

            $this->refreshReservationTotals($reservation->fresh(['items', 'promoCode']));
        }
    }

    private function topUpReservationItems(): void
    {
        $remaining = $this->remaining('reservation_items');
        if ($remaining === 0) {
            return;
        }

        $reservations = Reservation::query()->with('event.ticketTiers', 'promoCode')->get()->filter(
            fn (Reservation $reservation) => $reservation->event && $reservation->event->ticketTiers->isNotEmpty()
        )->values();

        if ($reservations->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $remaining; $i++) {
            $reservation = $reservations->random();
            $tier = $reservation->event->ticketTiers->random();
            $quantity = random_int(1, min(4, max(1, (int) $tier->max_per_order)));

            ReservationItem::query()->create([
                'reservation_id' => $reservation->id,
                'ticket_tier_id' => $tier->id,
                'quantity' => $quantity,
                'unit_price' => $tier->price,
                'subtotal' => round($quantity * (float) $tier->price, 2),
            ]);

            $this->refreshReservationTotals($reservation->fresh(['items', 'promoCode']));
        }
    }

    private function topUpBookings(): void
    {
        $remaining = $this->remaining('bookings');
        if ($remaining === 0) {
            return;
        }

        $events = Event::query()->with('ticketTiers')->get()->filter(
            fn (Event $event) => $event->ticketTiers->isNotEmpty()
        )->values();
        $customers = Customer::query()->get()->values();
        $reservations = Reservation::query()->with('items.ticketTier')->get()->values();
        $promoCodes = PromoCode::query()->get()->values();

        if ($events->isEmpty() || $customers->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $remaining; $i++) {
            $customer = $customers->random();
            $reservation = $reservations->isNotEmpty() && fake()->boolean(45) ? $reservations->random() : null;
            $event = $reservation?->event_id
                ? $events->firstWhere('id', $reservation->event_id)
                : $events->random();

            if (!$event) {
                $event = $events->random();
                $reservation = null;
            }

            $promoCodeId = $reservation?->promo_code_id ?: $this->pickPromoForEvent($promoCodes, $event)?->id;

            $booking = Booking::query()->create([
                'event_id' => $event->id,
                'reservation_id' => $reservation?->id,
                'customer_id' => $customer->id,
                'promo_code_id' => $promoCodeId,
                'customer_name' => $reservation?->customer_name ?: $customer->name,
                'customer_email' => $reservation?->customer_email ?: $customer->email,
                'customer_phone' => $reservation?->customer_phone ?: $customer->phone,
                'subtotal' => 0,
                'discount_amount' => 0,
                'portal_fee' => 0,
                'service_fee' => 0,
                'total' => 0,
                'currency' => ticketly_currency(),
                'stripe_session_id' => null,
                'stripe_payment_intent_id' => null,
                'stripe_charge_id' => null,
                'status' => 'pending',
                'refund_amount' => null,
                'refunded_at' => null,
                'refund_reason' => null,
                'is_used' => false,
                'confirmation_sent_at' => null,
                'reminders_sent' => [],
                'scanned_at' => null,
                'scanned_quantity' => 0,
            ]);

            $sourceItems = $reservation?->items?->isNotEmpty()
                ? $reservation->items
                : $event->ticketTiers->shuffle()->take(random_int(1, min(3, $event->ticketTiers->count())));

            foreach ($sourceItems as $sourceItem) {
                $tier = $sourceItem instanceof ReservationItem ? $sourceItem->ticketTier : $sourceItem;
                if (!$tier) {
                    continue;
                }

                $quantity = $sourceItem instanceof ReservationItem
                    ? $sourceItem->quantity
                    : random_int(1, min(4, max(1, (int) $tier->max_per_order)));

                BookingItem::query()->create([
                    'booking_id' => $booking->id,
                    'ticket_tier_id' => $tier->id,
                    'quantity' => $quantity,
                    'unit_price' => $tier->price,
                    'subtotal' => round($quantity * (float) $tier->price, 2),
                ]);
            }

            $this->refreshBookingTotals($booking->fresh(['items', 'promoCode', 'event']));
            $this->applyBookingStatus($booking->fresh(['items', 'event']));
        }
    }

    private function topUpBookingItems(): void
    {
        $remaining = $this->remaining('booking_items');
        if ($remaining === 0) {
            return;
        }

        $bookings = Booking::query()->with('event.ticketTiers', 'items', 'promoCode')->get()->filter(
            fn (Booking $booking) => $booking->event && $booking->event->ticketTiers->isNotEmpty()
        )->values();

        if ($bookings->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $remaining; $i++) {
            $booking = $bookings->random();
            $tier = $booking->event->ticketTiers->random();
            $quantity = random_int(1, min(4, max(1, (int) $tier->max_per_order)));

            BookingItem::query()->create([
                'booking_id' => $booking->id,
                'ticket_tier_id' => $tier->id,
                'quantity' => $quantity,
                'unit_price' => $tier->price,
                'subtotal' => round($quantity * (float) $tier->price, 2),
            ]);

            $this->refreshBookingTotals($booking->fresh(['items', 'promoCode', 'event']));
            $this->normalizeBookingScanState($booking->fresh(['items', 'event']));
        }
    }

    private function topUpSponsorships(): void
    {
        $remaining = $this->remaining('sponsorships');
        if ($remaining === 0) {
            return;
        }

        $events = Event::query()->get();
        if ($events->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $remaining; $i++) {
            Sponsorship::query()->create([
                'event_id' => $events->random()->id,
                'name' => fake()->company(),
                'photo' => null,
            ]);
        }
    }

    private function topUpPayouts(): void
    {
        $remaining = $this->remaining('payouts');
        if ($remaining === 0) {
            return;
        }

        $organisers = Organiser::query()->get();
        if ($organisers->isEmpty()) {
            return;
        }

        $start = DB::table('payouts')->count() + 1;

        for ($i = 0; $i < $remaining; $i++) {
            $index = $start + $i;

            Payout::query()->create([
                'user_id' => $organisers->random()->id,
                'stripe_payout_id' => 'po_demo_' . str_pad((string) $index, 6, '0', STR_PAD_LEFT),
                'amount' => random_int(3000, 50000),
                'currency' => ticketly_currency(),
                'status' => fake()->boolean(55) ? 'paid_out' : 'pending',
            ]);
        }
    }

    private function topUpSystemSettings(): void
    {
        $seedSettings = [
            ['key' => 'service_fee_percentage', 'value' => 5.0, 'type' => 'float'],
            ['key' => 'portal_fee_percentage', 'value' => 10.0, 'type' => 'float'],
            ['key' => 'settlement_days', 'value' => 7, 'type' => 'integer'],
            ['key' => 'support_email', 'value' => 'support@example.test', 'type' => 'string'],
            ['key' => 'admin_email', 'value' => 'admin@example.test', 'type' => 'string'],
            ['key' => 'mail_from_address', 'value' => 'no-reply@example.test', 'type' => 'string'],
            ['key' => 'mail_from_name', 'value' => 'Ticket Demo', 'type' => 'string'],
            ['key' => 'allow_free_events', 'value' => true, 'type' => 'boolean'],
            ['key' => 'homepage_featured_limit', 'value' => 8, 'type' => 'integer'],
            ['key' => 'default_timezone', 'value' => 'Asia/Kolkata', 'type' => 'string'],
        ];

        foreach ($seedSettings as $setting) {
            SystemSetting::setValue($setting['key'], $setting['value'], $setting['type'], null);
        }

        $counter = 1;
        while (DB::table('system_settings')->count() < self::TARGET_COUNT) {
            $key = 'demo_setting_' . str_pad((string) $counter, 3, '0', STR_PAD_LEFT);
            $counter++;

            if (SystemSetting::query()->where('key', $key)->exists()) {
                continue;
            }

            $type = fake()->randomElement(['string', 'integer', 'float', 'boolean', 'json']);
            $value = match ($type) {
                'integer' => random_int(1, 999),
                'float' => round(fake()->randomFloat(2, 1, 250), 2),
                'boolean' => fake()->boolean(),
                'json' => ['label' => fake()->word(), 'enabled' => fake()->boolean()],
                default => fake()->sentence(4),
            };

            SystemSetting::setValue($key, $value, $type, null);
        }
    }

    private function topUpEmailLogs(): void
    {
        $remaining = $this->remaining('email_logs');
        if ($remaining === 0) {
            return;
        }

        $contexts = collect()
            ->concat(Event::query()->limit(20)->get())
            ->concat(Reservation::query()->limit(20)->get())
            ->concat(Booking::query()->limit(20)->get())
            ->concat(PromoCode::query()->limit(20)->get())
            ->values();

        for ($i = 0; $i < $remaining; $i++) {
            $status = $this->randomWeightedValue([
                'sent' => 70,
                'queued' => 20,
                'failed' => 10,
            ]);
            $context = $contexts->isNotEmpty() ? $contexts->random() : null;

            EmailLog::query()->create([
                'to' => fake()->safeEmail(),
                'subject' => fake()->sentence(5),
                'status' => $status,
                'mailable' => fake()->randomElement([
                    'BookingConfirmed',
                    'BookingReminder',
                    'RefundProcessed',
                    'OrganiserApproved',
                ]),
                'context_type' => $context?->getMorphClass(),
                'context_id' => $context?->getKey(),
                'error' => $status === 'failed' ? fake()->sentence() : null,
                'meta' => [
                    'source' => 'fake-project-data-seeder',
                    'batch' => now()->format('Ymd'),
                ],
                'sent_at' => $status === 'sent' ? now()->subMinutes(random_int(1, 30000)) : null,
            ]);
        }
    }

    private function topUpVisitorLogs(): void
    {
        $remaining = $this->remaining('visitor_logs');
        if ($remaining === 0) {
            return;
        }

        $users = User::query()->pluck('id')->all();
        $organisers = Organiser::query()->pluck('id')->all();

        for ($i = 0; $i < $remaining; $i++) {
            $route = fake()->randomElement(self::ROUTES);
            $secure = fake()->boolean(60);
            $query = fake()->boolean(35) ? 'page=' . random_int(1, 5) : null;

            VisitorLog::query()->create([
                'ip_address' => fake()->ipv4(),
                'city' => fake()->city(),
                'region' => fake()->state(),
                'country' => 'India',
                'country_code' => 'IN',
                'latitude' => fake()->latitude(8.0, 32.0),
                'longitude' => fake()->longitude(68.0, 89.0),
                'timezone' => 'Asia/Kolkata',
                'method' => fake()->randomElement(['GET', 'POST']),
                'host' => '127.0.0.1:8000',
                'path' => $route['path'],
                'full_url' => 'http' . ($secure ? 's' : '') . '://127.0.0.1:8000' . $route['path'] . ($query ? '?' . $query : ''),
                'query' => $query,
                'route_name' => $route['name'],
                'route_uri' => $route['uri'],
                'user_agent' => fake()->userAgent(),
                'referer' => fake()->boolean(50) ? 'https://google.com/search?q=events' : null,
                'accept_language' => 'en-IN,en;q=0.9',
                'session_id' => Str::random(40),
                'user_id' => !empty($users) && fake()->boolean(30) ? fake()->randomElement($users) : null,
                'organiser_id' => !empty($organisers) && fake()->boolean(20) ? fake()->randomElement($organisers) : null,
                'admin_id' => null,
                'is_secure' => $secure,
                'response_status' => fake()->randomElement([200, 200, 200, 201, 302, 404]),
            ]);
        }
    }

    private function topUpBookingRefunds(): void
    {
        $remaining = $this->remaining('booking_refunds');
        if ($remaining === 0) {
            return;
        }

        $created = 0;
        $attempts = 0;

        while ($created < $remaining && $attempts < ($remaining * 10)) {
            $attempts++;
            $booking = Booking::query()->where('total', '>', 0)->inRandomOrder()->first();
            if (!$booking) {
                break;
            }

            $alreadyRefunded = (float) BookingRefund::query()
                ->where('booking_id', $booking->id)
                ->sum('refunded_amount');

            $remainingTotal = max(0, round((float) $booking->total - $alreadyRefunded, 2));
            if ($remainingTotal <= 0) {
                continue;
            }

            $refundedAmount = $remainingTotal <= 1
                ? $remainingTotal
                : round(fake()->randomFloat(2, 1, $remainingTotal), 2);
            $newRemaining = max(0, round($remainingTotal - $refundedAmount, 2));

            BookingRefund::query()->create([
                'booking_id' => $booking->id,
                'stripe_refund_id' => 're_demo_' . Str::lower(Str::random(18)),
                'original_total' => $booking->total,
                'refunded_amount' => $refundedAmount,
                'remaining_total' => $newRemaining,
                'currency' => $booking->currency,
                'reason' => fake()->sentence(),
                'refunded_at' => now()->subDays(random_int(0, 45)),
            ]);

            $totalRefunded = round($alreadyRefunded + $refundedAmount, 2);
            $booking->update([
                'refund_amount' => $totalRefunded,
                'refunded_at' => now()->subDays(random_int(0, 45)),
                'refund_reason' => fake()->sentence(),
                'status' => $newRemaining <= 0 ? 'refunded' : 'partially_refunded',
            ]);

            $created++;
        }
    }

    private function createTierForEvent(Event $event, int $sortOrder): TicketTier
    {
        $totalQuantity = random_int(60, 600);
        $maxPerOrder = random_int(2, 8);

        return TicketTier::query()->create([
            'event_id' => $event->id,
            'name' => fake()->randomElement(self::TIER_NAMES),
            'description' => fake()->optional(0.55)->sentence(),
            'price' => round(fake()->randomFloat(2, 0, 7500), 2),
            'total_quantity' => $totalQuantity,
            'available_quantity' => $totalQuantity,
            'min_per_order' => 1,
            'max_per_order' => $maxPerOrder,
            'is_active' => fake()->boolean(90),
            'sort_order' => $sortOrder,
        ]);
    }

    private function refreshReservationTotals(Reservation $reservation): void
    {
        $subtotal = round((float) $reservation->items->sum('subtotal'), 2);
        $pricing = ServiceFeeCalculator::totalForPromo($subtotal, $reservation->promoCode);

        $reservation->update([
            'subtotal' => $pricing['subtotal'],
            'discount_amount' => $pricing['discount'],
            'service_fee' => $pricing['service_fee'],
            'portal_fee' => $pricing['portal_fee'],
            'total' => $pricing['total'],
        ]);
    }

    private function refreshBookingTotals(Booking $booking): void
    {
        $subtotal = round((float) $booking->items->sum('subtotal'), 2);
        $pricing = ServiceFeeCalculator::totalForPromo($subtotal, $booking->promoCode);

        $booking->update([
            'subtotal' => $pricing['subtotal'],
            'discount_amount' => $pricing['discount'],
            'service_fee' => $pricing['service_fee'],
            'portal_fee' => $pricing['portal_fee'],
            'total' => $pricing['total'],
            'currency' => ticketly_currency(),
        ]);
    }

    private function applyBookingStatus(Booking $booking): void
    {
        $status = $this->randomWeightedValue([
            'paid' => 70,
            'pending' => 15,
            'cancelled' => 8,
            'failed' => 7,
        ]);

        $booking->update([
            'status' => $status,
            'refund_amount' => null,
            'refunded_at' => null,
            'refund_reason' => null,
            'stripe_session_id' => in_array($status, ['paid', 'pending'], true) ? 'cs_demo_' . Str::lower(Str::random(22)) : null,
            'stripe_payment_intent_id' => in_array($status, ['paid', 'pending', 'failed'], true) ? 'pi_demo_' . Str::lower(Str::random(22)) : null,
            'stripe_charge_id' => $status === 'paid' ? 'ch_demo_' . Str::lower(Str::random(20)) : null,
            'confirmation_sent_at' => $status === 'paid' ? now()->subMinutes(random_int(10, 50000)) : null,
            'reminders_sent' => $status === 'paid' && fake()->boolean(45)
                ? [now()->subDays(random_int(1, 7))->toDateTimeString()]
                : [],
        ]);

        $this->normalizeBookingScanState($booking->fresh(['items', 'event']));
    }

    private function normalizeBookingScanState(Booking $booking): void
    {
        $ticketQuantity = max(1, (int) $booking->items->sum('quantity'));
        $eventStarted = $booking->event?->starts_at instanceof CarbonInterface
            ? $booking->event->starts_at->isPast()
            : false;

        $scannedQuantity = 0;
        $isUsed = false;
        $scannedAt = null;

        if ($booking->status === 'paid' && $eventStarted && fake()->boolean(45)) {
            $scannedQuantity = random_int(1, $ticketQuantity);
            $isUsed = true;
            $scannedAt = now()->subMinutes(random_int(5, 10000));
        }

        $booking->update([
            'scanned_quantity' => $scannedQuantity,
            'is_used' => $isUsed,
            'scanned_at' => $scannedAt,
        ]);
    }

    private function syncTicketInventory(): void
    {
        $soldByTier = BookingItem::query()
            ->selectRaw('ticket_tier_id, SUM(quantity) as sold_quantity')
            ->groupBy('ticket_tier_id')
            ->pluck('sold_quantity', 'ticket_tier_id');

        TicketTier::query()->each(function (TicketTier $tier) use ($soldByTier) {
            $sold = (int) ($soldByTier[$tier->id] ?? 0);
            $available = max(0, (int) $tier->total_quantity - $sold);

            if ((int) $tier->available_quantity !== $available) {
                $tier->update(['available_quantity' => $available]);
            }
        });
    }

    private function syncEventCapacities(): void
    {
        Event::query()->each(function (Event $event) {
            $this->refreshEventCapacity($event);
        });
    }

    private function refreshEventCapacity(Event $event): void
    {
        $totalCapacity = (int) $event->ticketTiers()->sum('total_quantity');

        if ((int) $event->total_capacity !== $totalCapacity) {
            $event->update(['total_capacity' => $totalCapacity]);
        }
    }

    private function pickPromoForEvent(Collection $promoCodes, Event $event): ?PromoCode
    {
        $eligible = $promoCodes->filter(function (PromoCode $promoCode) use ($event) {
            if ((int) $promoCode->organiser_id !== (int) $event->organiser_id) {
                return false;
            }

            if ($promoCode->event_id !== null && (int) $promoCode->event_id !== (int) $event->id) {
                return false;
            }

            return (bool) $promoCode->is_active;
        })->values();

        if ($eligible->isEmpty() || fake()->boolean(60) === false) {
            return null;
        }

        return $eligible->random();
    }

    private function remaining(string $table): int
    {
        return max(0, self::TARGET_COUNT - (int) DB::table($table)->count());
    }

    private function eventStatusFor(CarbonInterface $startsAt): string
    {
        if ($startsAt->isPast()) {
            return $this->randomWeightedValue([
                'published' => 60,
                'cancelled' => 10,
                'draft' => 30,
            ]);
        }

        return $this->randomWeightedValue([
            'published' => 68,
            'draft' => 25,
            'cancelled' => 7,
        ]);
    }

    private function randomEventStart(): CarbonInterface
    {
        $dayOffset = random_int(-90, 180);
        $minutes = [0, 15, 30, 45];

        return now()
            ->copy()
            ->addDays($dayOffset)
            ->setTime(random_int(9, 21), $minutes[array_rand($minutes)]);
    }

    private function randomWeightedValue(array $weights): string
    {
        $roll = random_int(1, array_sum($weights));
        $running = 0;

        foreach ($weights as $value => $weight) {
            $running += $weight;
            if ($roll <= $running) {
                return (string) $value;
            }
        }

        return (string) array_key_first($weights);
    }

    private function fakePhone(): string
    {
        return '+91' . random_int(6000000000, 9999999999);
    }
}
