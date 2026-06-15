<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\Sponsorship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OrganiserSponsorshipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    public function test_organiser_can_create_a_sponsorship_with_photo(): void
    {
        Storage::fake('public');

        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->post(route('organiser.sponsorships.store', $event->id), [
                'name' => 'Main Brand Partner',
                'photo' => UploadedFile::fake()->image('brand.png'),
            ]);

        $response->assertRedirect(route('organiser.sponsorships.index', $event->id));
        $response->assertSessionHas('success', 'Sponsorship added successfully.');

        $sponsorship = Sponsorship::firstOrFail();

        $this->assertSame($event->id, $sponsorship->event_id);
        $this->assertSame('Main Brand Partner', $sponsorship->name);
        Storage::disk('public')->assertExists($sponsorship->photo);
    }

    public function test_organiser_events_index_hides_sponsor_preview(): void
    {
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);

        Sponsorship::create([
            'event_id' => $event->id,
            'name' => 'Visible Sponsor',
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->get(route('organiser.events.index'));

        $response->assertOk();
        $response->assertDontSee('Visible Sponsor');
        $response->assertDontSee('No sponsors added yet for this event.');
        $response->assertDontSee(route('organiser.sponsorships.create', $event->id));
        $response->assertSee(route('organiser.sponsorships.index', $event->id));
    }

    public function test_organiser_can_view_sponsorship_management_page(): void
    {
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);

        Sponsorship::create([
            'event_id' => $event->id,
            'name' => 'Visible Sponsor',
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->get(route('organiser.sponsorships.index', $event->id));

        $response->assertOk();
        $response->assertSee('Visible Sponsor');
        $response->assertSee(route('organiser.sponsorships.create', $event->id));
        $response->assertSee(route('organiser.sponsorships.edit', [$event->id, Sponsorship::firstOrFail()->id]));
    }

    public function test_organiser_event_edit_page_has_manage_sponsorship_button(): void
    {
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->get(route('organiser.events.edit', $event->id));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Ticket Tiers',
            'Manage Sponsorship',
            'Preview',
        ]);
        $response->assertSee(route('organiser.sponsorships.index', $event->id));
    }

    public function test_sponsorship_create_page_uses_updated_helper_text(): void
    {
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->get(route('organiser.sponsorships.create', $event->id));

        $response->assertOk();
        $response->assertSee('Allowed types: jpg, jpeg, png, webp.');
        $response->assertDontSee('Stored in public storage');
    }

    public function test_organiser_can_update_a_sponsorship_and_replace_photo(): void
    {
        Storage::fake('public');

        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);
        $oldPath = 'sponsorships/original-logo.png';
        Storage::disk('public')->put($oldPath, 'old-image');

        $sponsorship = Sponsorship::create([
            'event_id' => $event->id,
            'name' => 'Original Sponsor',
            'photo' => $oldPath,
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->put(route('organiser.sponsorships.update', [$event->id, $sponsorship->id]), [
                'name' => 'Updated Sponsor',
                'photo' => UploadedFile::fake()->image('updated.webp'),
            ]);

        $response->assertRedirect(route('organiser.sponsorships.index', $event->id));
        $response->assertSessionHas('success', 'Sponsorship updated successfully.');

        $sponsorship->refresh();

        $this->assertSame('Updated Sponsor', $sponsorship->name);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($sponsorship->photo);
    }

    public function test_organiser_can_delete_a_sponsorship_and_its_photo(): void
    {
        Storage::fake('public');

        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);
        $photoPath = 'sponsorships/remove-me.jpg';
        Storage::disk('public')->put($photoPath, 'delete-me');

        $sponsorship = Sponsorship::create([
            'event_id' => $event->id,
            'name' => 'Delete Sponsor',
            'photo' => $photoPath,
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->delete(route('organiser.sponsorships.destroy', [$event->id, $sponsorship->id]));

        $response->assertRedirect(route('organiser.sponsorships.index', $event->id));
        $response->assertSessionHas('success', 'Sponsorship deleted successfully.');

        $this->assertDatabaseMissing('sponsorships', [
            'id' => $sponsorship->id,
        ]);
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_organiser_cannot_manage_another_organisers_event_sponsorships(): void
    {
        $organiser = $this->makeOrganiser([
            'email' => 'first.organiser@gmail.com',
            'phone' => '07123456780',
        ]);
        $otherOrganiser = $this->makeOrganiser([
            'email' => 'second.organiser@gmail.com',
            'phone' => '07123456781',
        ]);
        $otherEvent = $this->makeEvent($otherOrganiser, [
            'title' => 'Other Event',
            'slug' => 'other-event',
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->post(route('organiser.sponsorships.store', $otherEvent->id), [
                'name' => 'Blocked Sponsor',
            ]);

        $response->assertNotFound();
    }

    public function test_public_event_detail_displays_sponsor_logos_section(): void
    {
        Storage::fake('public');
        config([
            'app.url' => 'http://localhost',
            'filesystems.disks.public.url' => 'http://localhost/storage',
        ]);

        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser, [
            'status' => 'published',
            'approval_status' => 'approved',
            'slug' => 'public-sponsor-event',
        ]);

        $photo = UploadedFile::fake()->image('logo.png');
        $path = $photo->store('sponsorships', 'public');

        Sponsorship::create([
            'event_id' => $event->id,
            'name' => 'Public Sponsor',
            'photo' => $path,
        ]);

        URL::forceRootUrl('http://127.0.0.1:8000');

        $response = $this->get('/events/' . $event->slug);

        $response->assertOk();
        $response->assertSeeInOrder([
            'How would you like to get there?',
            'Event Sponsors',
        ]);
        $response->assertSee('http://127.0.0.1:8000/storage/' . $path);
        $response->assertDontSee('http://localhost/storage/' . $path);
        $response->assertDontSee('Sponsored By');

        URL::forceRootUrl(null);
    }

    public function test_admin_event_detail_displays_sponsors_in_view_only_grid(): void
    {
        Storage::fake('public');
        config([
            'app.url' => 'http://localhost',
            'filesystems.disks.public.url' => 'http://localhost/storage',
        ]);

        $admin = $this->makeAdmin();
        $organiser = $this->makeOrganiser();
        $event = $this->makeEvent($organiser);
        $photo = UploadedFile::fake()->image('admin-logo.png');
        $path = $photo->store('sponsorships', 'public');

        $sponsorship = Sponsorship::create([
            'event_id' => $event->id,
            'name' => 'Admin Sponsor',
            'photo' => $path,
        ]);

        $response = $this
            ->withSession($this->adminSession($admin))
            ->get(route('admin.events.show', $event->id));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Ticket Tiers',
            'Sponsors',
        ]);
        $response->assertSee('Admin Sponsor');
        $response->assertSee('storage/' . $path);
        $response->assertDontSee(route('organiser.sponsorships.edit', [$event->id, $sponsorship->id]));
        $response->assertDontSee(route('organiser.sponsorships.destroy', [$event->id, $sponsorship->id]));
    }

    private function makeAdmin(array $overrides = []): Admin
    {
        return Admin::create(array_merge([
            'name' => 'Sponsor Admin',
            'email' => 'sponsor.admin@example.com',
            'password' => Hash::make('password123'),
        ], $overrides));
    }

    private function makeOrganiser(array $overrides = []): Organiser
    {
        return Organiser::create(array_merge([
            'name' => 'Sponsor Organiser',
            'company_name' => 'Sponsor Co',
            'email' => 'sponsor.organiser@gmail.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ], $overrides));
    }

    private function makeEvent(Organiser $organiser, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'organiser_id' => $organiser->id,
            'title' => 'Sponsor Ready Event',
            'slug' => 'sponsor-ready-event',
            'description' => 'Event description',
            'short_description' => 'Short description',
            'venue_name' => 'Main Hall',
            'venue_address' => '123 Event Street',
            'city' => 'London',
            'country' => 'GB',
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(10)->addHours(4),
            'ticket_validation_starts_at' => now()->addDays(10)->subHour(),
            'ticket_validation_ends_at' => now()->addDays(10)->addHours(4),
            'category' => 'Music',
            'status' => 'draft',
            'approval_status' => 'approved',
            'total_capacity' => 100,
        ], $overrides));
    }

    private function organiserSession(Organiser $organiser): array
    {
        return [
            'organiser_id' => $organiser->id,
            'organiser_last_active' => now()->timestamp,
        ];
    }

    private function adminSession(Admin $admin): array
    {
        return [
            'admin_id' => $admin->id,
            'admin_last_active' => now()->timestamp,
        ];
    }
}
