<?php

namespace App\Http\Controllers\Organiser;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Sponsorship;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SponsorshipController extends Controller
{
    public function index(Request $request, int $eventId)
    {
        $event = $this->findOwnedEvent($request, $eventId)->load('sponsorships');

        return view('organiser.sponsorships.index', [
            'event' => $event,
            'sponsorships' => $event->sponsorships,
        ]);
    }

    public function create(Request $request, int $eventId)
    {
        $event = $this->findOwnedEvent($request, $eventId);

        return view('organiser.sponsorships.create', [
            'event' => $event,
        ]);
    }

    public function store(Request $request, int $eventId)
    {
        $event = $this->findOwnedEvent($request, $eventId);
        $validated = $this->validateSponsorship($request);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $this->storePhoto($request->file('photo'));
        }

        $event->sponsorships()->create($validated);

        return redirect()
            ->route('organiser.sponsorships.index', $event->id)
            ->with('success', 'Sponsorship added successfully.');
    }

    public function edit(Request $request, int $eventId, int $id)
    {
        $event = $this->findOwnedEvent($request, $eventId);
        $sponsorship = $this->findEventSponsorship($event, $id);

        return view('organiser.sponsorships.edit', [
            'event' => $event,
            'sponsorship' => $sponsorship,
        ]);
    }

    public function update(Request $request, int $eventId, int $id)
    {
        $event = $this->findOwnedEvent($request, $eventId);
        $sponsorship = $this->findEventSponsorship($event, $id);
        $validated = $this->validateSponsorship($request);

        if ($request->hasFile('photo')) {
            $this->deletePhoto($sponsorship->photo);
            $validated['photo'] = $this->storePhoto($request->file('photo'));
        }

        $sponsorship->update($validated);

        return redirect()
            ->route('organiser.sponsorships.index', $event->id)
            ->with('success', 'Sponsorship updated successfully.');
    }

    public function destroy(Request $request, int $eventId, int $id)
    {
        $event = $this->findOwnedEvent($request, $eventId);
        $sponsorship = $this->findEventSponsorship($event, $id);

        $this->deletePhoto($sponsorship->photo);
        $sponsorship->delete();

        return redirect()
            ->route('organiser.sponsorships.index', $event->id)
            ->with('success', 'Sponsorship deleted successfully.');
    }

    private function validateSponsorship(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
        ], [
            'name.required' => 'Sponsor name is required.',
            'name.max' => 'Sponsor name may not be greater than 100 characters.',
            'photo.image' => 'Sponsor photo must be an image.',
            'photo.mimes' => 'Sponsor photo must be a file of type: jpg, jpeg, png, webp.',
        ]);
    }

    private function findOwnedEvent(Request $request, int $eventId): Event
    {
        $organiser = $request->attributes->get('organiser');

        return Event::where('id', $eventId)
            ->where('organiser_id', $organiser->id)
            ->firstOrFail();
    }

    private function findEventSponsorship(Event $event, int $id): Sponsorship
    {
        return $event->sponsorships()->where('id', $id)->firstOrFail();
    }

    private function storePhoto(UploadedFile $file): string
    {
        return $file->store('sponsorships', 'public');
    }

    private function deletePhoto(?string $path): void
    {
        if (!$path) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
