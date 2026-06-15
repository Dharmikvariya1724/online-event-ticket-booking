<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organiser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrganiserEventLineupTimeValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    public function test_organiser_cannot_create_event_with_invalid_lineup_time(): void
    {
        $organiser = $this->makeOrganiser();

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->post(route('organiser.events.store'), $this->makeEventPayload([
                'lineup_names' => ['DJ Alpha'],
                'lineup_roles' => ['Headliner'],
                'lineup_times' => ['20-AA'],
            ]));

        $response->assertSessionHasErrors(['lineup_times.0']);
        $response->assertSessionHas('errors', function ($errors) {
            return $errors->first('lineup_times.0') === 'Time must be in valid HH:MM format (e.g. 20:00)';
        });
        $this->assertDatabaseCount('events', 0);
    }

    public function test_organiser_can_create_event_with_valid_lineup_time(): void
    {
        $organiser = $this->makeOrganiser();

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->post(route('organiser.events.store'), $this->makeEventPayload([
                'lineup_names' => ['DJ Alpha'],
                'lineup_roles' => ['Headliner'],
                'lineup_times' => ['20:00'],
            ]));

        $event = Event::firstOrFail();

        $response->assertRedirect(route('organiser.tiers.create', $event->id));
        $this->assertSame([
            [
                'name' => 'DJ Alpha',
                'role' => 'Headliner',
                'time' => '20:00',
            ],
        ], $event->performer_lineup);
    }

    public function test_organiser_cannot_update_event_with_invalid_lineup_time(): void
    {
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser, [
            'performer_lineup' => [
                [
                    'name' => 'DJ Alpha',
                    'role' => 'Headliner',
                    'time' => '19:00',
                ],
            ],
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->put(route('organiser.events.update', $event->id), $this->makeEventPayload([
                'lineup_names' => ['DJ Alpha'],
                'lineup_roles' => ['Headliner'],
                'lineup_times' => ['99:99'],
            ], $event));

        $response->assertSessionHasErrors(['lineup_times.0']);
        $response->assertSessionHas('errors', function ($errors) {
            return $errors->first('lineup_times.0') === 'Time must be in valid HH:MM format (e.g. 20:00)';
        });

        $event->refresh();
        $this->assertSame('19:00', $event->performer_lineup[0]['time']);
    }

    public function test_organiser_sees_custom_max_length_messages_for_lineup_name_and_role(): void
    {
        $organiser = $this->makeOrganiser();

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->post(route('organiser.events.store'), $this->makeEventPayload([
                'lineup_names' => [str_repeat('A', 51)],
                'lineup_roles' => [str_repeat('B', 51)],
                'lineup_times' => ['20:00'],
            ]));

        $response->assertSessionHasErrors(['lineup_names.0', 'lineup_roles.0']);
        $response->assertSessionHas('errors', function ($errors) {
            return $errors->first('lineup_names.0') === 'Performer Name maximum limit reached.'
                && $errors->first('lineup_roles.0') === 'Role / Band maximum limit reached.';
        });
    }

    private function makeOrganiser(array $overrides = []): Organiser
    {
        return Organiser::create(array_merge([
            'name' => 'Lineup Organiser',
            'company_name' => 'Lineup Co',
            'email' => 'lineup.organiser@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ], $overrides));
    }

    private function makeEvent(Organiser $organiser, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'organiser_id' => $organiser->id,
            'title' => 'Lineup Ready Event',
            'slug' => 'lineup-ready-event',
            'short_description' => 'Short description',
            'description' => 'Event description',
            'category' => 'Music',
            'starts_at' => now()->addDays(12),
            'ends_at' => now()->addDays(12)->addHours(4),
            'ticket_validation_starts_at' => now()->addDays(12)->subHours(2),
            'ticket_validation_ends_at' => now()->addDays(12)->addHours(4),
            'venue_name' => 'Main Hall',
            'venue_address' => '123 Event Street',
            'city' => 'London',
            'country' => 'GB',
            'postcode' => 'E1 6AN',
            'parking_info' => 'Parking nearby',
            'refund_policy' => 'Refunds available up to 48 hours before the event.',
            'status' => 'draft',
            'approval_status' => 'approved',
            'total_capacity' => 100,
        ], $overrides));
    }

    private function makeEventPayload(array $overrides = [], ?Event $event = null): array
    {
        $startsAt = $event?->starts_at?->copy() ?? now()->addDays(10);
        $endsAt = $event?->ends_at?->copy() ?? now()->addDays(10)->addHours(4);
        $validationStartsAt = $event?->ticket_validation_starts_at?->copy() ?? $startsAt->copy()->subHours(2);
        $validationEndsAt = $event?->ticket_validation_ends_at?->copy() ?? $endsAt->copy();

        return array_merge([
            'title' => $event?->title ?? 'Summer Music Festival 2026',
            'short_description' => $event?->short_description ?? 'A one-line summary',
            'description' => $event?->description ?? 'Full event description',
            'category' => $event?->category ?? 'Music',
            'starts_at' => $startsAt->format('Y-m-d H:i:s'),
            'ends_at' => $endsAt->format('Y-m-d H:i:s'),
            'ticket_validation_starts_at' => $validationStartsAt->format('Y-m-d H:i:s'),
            'ticket_validation_ends_at' => $validationEndsAt->format('Y-m-d H:i:s'),
            'venue_name' => $event?->venue_name ?? 'Main Hall',
            'venue_address' => $event?->venue_address ?? '123 Event Street',
            'city' => $event?->city ?? 'London',
            'country' => $event?->country ?? 'GB',
            'postcode' => $event?->postcode ?? 'E1 6AN',
            'parking_info' => $event?->parking_info ?? 'Parking nearby',
            'refund_policy' => $event?->refund_policy ?? 'Refunds available up to 48 hours before the event.',
            'status' => $event?->status ?? 'draft',
            'lineup_names' => [''],
            'lineup_roles' => [''],
            'lineup_times' => [''],
        ], $overrides);
    }

    private function organiserSession(Organiser $organiser): array
    {
        return [
            'organiser_id' => $organiser->id,
            'organiser_last_active' => now()->timestamp,
        ];
    }
}
