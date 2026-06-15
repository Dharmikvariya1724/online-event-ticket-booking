<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organiser;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PublicEventShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_event_page_preserves_description_breaks_and_multi_day_schedule(): void
    {
        $organiser = Organiser::create([
            'name' => 'Public Event Organiser',
            'company_name' => 'Public Event Co',
            'email' => 'public-event@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ]);

        $event = Event::create([
            'organiser_id' => $organiser->id,
            'title' => 'Overnight Festival',
            'slug' => 'overnight-festival',
            'short_description' => 'Late night event',
            'description' => "<p>First paragraph.</p>\n<p>Second paragraph.</p>",
            'category' => 'Music',
            'starts_at' => Carbon::parse('2026-03-28 17:00:00'),
            'ends_at' => Carbon::parse('2026-03-29 01:00:00'),
            'ticket_validation_starts_at' => Carbon::parse('2026-03-28 15:00:00'),
            'ticket_validation_ends_at' => Carbon::parse('2026-03-29 01:00:00'),
            'venue_name' => 'Main Hall',
            'venue_address' => '123 Event Street',
            'city' => 'Ahmedabad',
            'country' => 'India',
            'postcode' => '380001',
            'parking_info' => 'Parking nearby',
            'refund_policy' => 'Refunds allowed',
            'status' => 'published',
            'approval_status' => 'approved',
        ]);

        $response = $this->get(route('events.show', $event->slug));

        $response->assertOk();
        $response->assertSee('Start: 28 Mar 2026, 5:00 PM');
        $response->assertSee('End: 29 Mar 2026, 1:00 AM');
        $response->assertSee('<p>First paragraph.</p>', false);
        $response->assertSee('<p>Second paragraph.</p>', false);
    }
}
