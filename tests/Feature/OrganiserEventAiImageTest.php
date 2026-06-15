<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organiser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrganiserEventAiImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    public function test_organiser_can_create_event_with_banner_and_banner_image(): void
    {
        $organiser = $this->makeOrganiser();

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->post(route('organiser.events.store'), $this->makeEventPayload([
                'banner' => UploadedFile::fake()->image('event-banner.png', 1200, 630),
                'banner_image' => UploadedFile::fake()->image('event-ai-image.png', 1200, 1600),
            ]));

        $event = Event::firstOrFail();

        $response->assertRedirect(route('organiser.tiers.create', $event->id));
        $this->assertNotNull($event->banner);
        $this->assertNotNull($event->banner_image);
        $this->assertStringStartsWith('uploads/events/', $event->banner);
        $this->assertStringStartsWith('ai_image/', $event->banner_image);
        $this->assertFileExists(public_path($event->banner));
        $this->assertFileExists(public_path($event->banner_image));
    }

    public function test_edit_page_displays_current_ai_image_preview(): void
    {
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser, [
            'banner_image' => 'ai_image/current-ai-image.png',
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->get(route('organiser.events.edit', $event->id));

        $response->assertOk();
        $response->assertSee('Current AI image used for autofill.', false);
        $response->assertSee(asset('ai_image/current-ai-image.png'), false);
        $response->assertSee('Event Poster', false);
        $response->assertSee('AI Image', false);
    }

    private function makeOrganiser(array $overrides = []): Organiser
    {
        return Organiser::create(array_merge([
            'name' => 'AI Image Organiser',
            'company_name' => 'Poster Co',
            'email' => 'ai.image.organiser@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ], $overrides));
    }

    private function makeEvent(Organiser $organiser, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'organiser_id' => $organiser->id,
            'title' => 'AI Event',
            'slug' => 'ai-event',
            'short_description' => 'Short description',
            'description' => 'Event description',
            'category' => 'Music',
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(10)->addHours(4),
            'ticket_validation_starts_at' => now()->addDays(10)->subHours(2),
            'ticket_validation_ends_at' => now()->addDays(10)->addHours(4),
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
            'banner' => 'uploads/events/current-banner.png',
        ], $overrides));
    }

    private function makeEventPayload(array $overrides = []): array
    {
        $startsAt = now()->addDays(12);
        $endsAt = $startsAt->copy()->addHours(4);
        $validationStartsAt = $startsAt->copy()->subHours(2);
        $validationEndsAt = $endsAt->copy();

        return array_merge([
            'title' => 'Summer Music Festival 2026',
            'short_description' => 'A one-line summary',
            'description' => 'Full event description',
            'category' => 'Music',
            'starts_at' => $startsAt->format('Y-m-d H:i:s'),
            'ends_at' => $endsAt->format('Y-m-d H:i:s'),
            'ticket_validation_starts_at' => $validationStartsAt->format('Y-m-d H:i:s'),
            'ticket_validation_ends_at' => $validationEndsAt->format('Y-m-d H:i:s'),
            'venue_name' => 'Main Hall',
            'venue_address' => '123 Event Street',
            'city' => 'London',
            'country' => 'GB',
            'postcode' => 'E1 6AN',
            'parking_info' => 'Parking nearby',
            'refund_policy' => 'Refunds available up to 48 hours before the event.',
            'status' => 'draft',
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
