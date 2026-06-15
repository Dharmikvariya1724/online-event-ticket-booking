<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Event;
use App\Models\Organiser;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminEventUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    private function makeAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    private function makeOrganiser(): Organiser
    {
        return Organiser::create([
            'name' => 'Event Organiser',
            'company_name' => 'Event Co',
            'email' => 'organiser@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ]);
    }

    private function makeEvent(Organiser $organiser): Event
    {
        return Event::create([
            'organiser_id' => $organiser->id,
            'title' => 'Original Event',
            'slug' => 'original-event',
            'short_description' => 'Original short description',
            'description' => 'Original description',
            'category' => 'Music',
            'starts_at' => Carbon::parse('2026-05-20 18:00:00'),
            'ends_at' => Carbon::parse('2026-05-20 22:00:00'),
            'ticket_validation_starts_at' => Carbon::parse('2026-05-20 16:00:00'),
            'ticket_validation_ends_at' => Carbon::parse('2026-05-20 22:00:00'),
            'venue_name' => 'Original Venue',
            'venue_address' => '123 Main Street',
            'city' => 'Ahmedabad',
            'country' => 'India',
            'postcode' => '380001',
            'parking_info' => 'Original parking info',
            'performer_lineup' => [
                [
                    'name' => 'DJ Nova',
                    'role' => 'Headliner',
                    'time' => '20:00',
                ],
            ],
            'refund_policy' => 'Original refund policy',
            'status' => 'draft',
            'approval_status' => 'approved',
        ]);
    }

    private function adminSession(Admin $admin): array
    {
        return [
            'admin_id' => $admin->id,
            'admin_last_active' => now()->timestamp,
        ];
    }

    public function test_admin_event_update_redirects_to_index_with_success_message(): void
    {
        $admin = $this->makeAdmin();
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);

        $response = $this
            ->withSession($this->adminSession($admin))
            ->put('/admin/events/' . $event->id, [
                'title' => 'Updated Admin Event',
                'category' => 'Comedy',
                'starts_at' => '2026-05-21T18:00',
                'ends_at' => '2026-05-21T22:00',
                'ticket_validation_starts_at' => '2026-05-21T16:00',
                'ticket_validation_ends_at' => '2026-05-21T22:00',
                'venue_name' => 'Updated Venue',
                'venue_address' => '456 Market Road',
                'city' => 'Surat',
                'postcode' => '395003',
                'short_description' => 'Updated short description',
                'description' => 'Updated description',
                'parking_info' => 'Updated parking info',
                'refund_policy' => 'Updated refund policy',
                'is_featured' => '1',
            ]);

        $response->assertRedirect(route('admin.events.index'));
        $response->assertSessionHas('success', 'Event updated successfully.');

        $event->refresh();

        $this->assertSame('Updated Admin Event', $event->title);
        $this->assertSame('Comedy', $event->category);
        $this->assertSame('Updated Venue', $event->venue_name);
        $this->assertTrue($event->is_featured);
        $this->assertSame('2026-05-21 16:00:00', $event->ticket_validation_starts_at?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-05-21 22:00:00', $event->ticket_validation_ends_at?->format('Y-m-d H:i:s'));
    }

    public function test_admin_event_update_returns_to_edit_page_with_validation_errors(): void
    {
        $admin = $this->makeAdmin();
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);

        $response = $this
            ->withSession($this->adminSession($admin))
            ->from('/admin/events/' . $event->id . '/edit')
            ->put('/admin/events/' . $event->id, [
                'title' => str_repeat('A', 51),
                'category' => 'Music',
                'starts_at' => '2026-05-21T18:00',
                'ends_at' => '2026-05-21T22:00',
                'ticket_validation_starts_at' => '',
                'ticket_validation_ends_at' => '2026-05-21T22:00',
                'venue_name' => 'Updated Venue',
                'venue_address' => '456 Market Road',
                'city' => 'Surat',
                'postcode' => '395003',
                'short_description' => 'Updated short description',
                'description' => 'Updated description',
                'parking_info' => 'Updated parking info',
                'refund_policy' => 'Updated refund policy',
            ]);

        $response->assertRedirect('/admin/events/' . $event->id . '/edit');
        $response->assertSessionHasErrors(['title', 'ticket_validation_starts_at']);

        $event->refresh();

        $this->assertSame('Original Event', $event->title);
        $this->assertSame('2026-05-20 16:00:00', $event->ticket_validation_starts_at?->format('Y-m-d H:i:s'));
    }

    public function test_admin_event_detail_displays_performer_details_after_policies_and_info(): void
    {
        $admin = $this->makeAdmin();
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);

        $response = $this
            ->withSession($this->adminSession($admin))
            ->get(route('admin.events.show', $event->id));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Policies & Info',
            'Performer Details',
            'Performer Name',
            'DJ Nova',
            'Role / Band',
            'Headliner',
            'Time',
            '20:00',
        ]);
    }

    public function test_admin_event_update_accepts_long_parking_info(): void
    {
        $admin = $this->makeAdmin();
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);
        $parkingInfo = trim(str_repeat('Parking guidance line. ', 40));

        $response = $this
            ->withSession($this->adminSession($admin))
            ->put('/admin/events/' . $event->id, [
                'title' => 'Updated Admin Event',
                'category' => 'Comedy',
                'starts_at' => '2026-05-21T18:00',
                'ends_at' => '2026-05-21T22:00',
                'ticket_validation_starts_at' => '2026-05-21T16:00',
                'ticket_validation_ends_at' => '2026-05-21T22:00',
                'venue_name' => 'Updated Venue',
                'venue_address' => '456 Market Road',
                'city' => 'Surat',
                'postcode' => '395003',
                'short_description' => 'Updated short description',
                'description' => 'Updated description',
                'parking_info' => $parkingInfo,
                'refund_policy' => 'Updated refund policy',
            ]);

        $response->assertRedirect(route('admin.events.index'));

        $event->refresh();
        $this->assertSame($parkingInfo, $event->parking_info);
    }
}
