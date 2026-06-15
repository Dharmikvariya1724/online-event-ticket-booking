<?php

namespace App\Http\Controllers\Organiser;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketTierController extends Controller
{
    public function index(Request $request, int $eventId)
    {
        $organiser = $request->attributes->get('organiser');
        $event     = Event::where('id', $eventId)->where('organiser_id', $organiser->id)->firstOrFail();
        $tiers     = $event->ticketTiers()->orderBy('sort_order')->get();

        return view('organiser.tiers.index', compact('organiser', 'event', 'tiers'));
    }

    public function create(Request $request, int $eventId)
    {
        $organiser = $request->attributes->get('organiser');
        $event     = Event::where('id', $eventId)->where('organiser_id', $organiser->id)->firstOrFail();
        return view('organiser.tiers.create', compact('organiser', 'event'));
    }

    public function store(Request $request, int $eventId)
    {
        $organiser = $request->attributes->get('organiser');
        $event     = Event::where('id', $eventId)->where('organiser_id', $organiser->id)->firstOrFail();

        $validated = $this->validateTier($request);
        $validated['event_id']           = $event->id;
        $validated['available_quantity'] = $validated['total_quantity'];

        TicketTier::create($validated);

        // Update event total_capacity
        $this->syncEventCapacity($event);

        return redirect()->route('organiser.tiers.index', $eventId)
            ->with('success', 'Ticket tier added.');
    }

    public function edit(Request $request, int $eventId, int $id)
    {
        $organiser = $request->attributes->get('organiser');
        $event     = Event::where('id', $eventId)->where('organiser_id', $organiser->id)->firstOrFail();
        $tier      = TicketTier::where('id', $id)->where('event_id', $event->id)->firstOrFail();
        return view('organiser.tiers.edit', compact('organiser', 'event', 'tier'));
    }

    public function update(Request $request, int $eventId, int $id)
    {
        $organiser = $request->attributes->get('organiser');
        $event     = Event::where('id', $eventId)->where('organiser_id', $organiser->id)->firstOrFail();
        $tier      = TicketTier::where('id', $id)->where('event_id', $event->id)->firstOrFail();

        $validated = $this->validateTier($request);

        // Adjust available_quantity by the difference in total
        $diff = (int)$validated['total_quantity'] - $tier->total_quantity;
        $validated['available_quantity'] = max(0, $tier->available_quantity + $diff);

        $tier->update($validated);
        $this->syncEventCapacity($event);

        return redirect()->route('organiser.tiers.index', $eventId)
            ->with('success', 'Ticket tier updated.');
    }

    public function destroy(Request $request, int $eventId, int $id)
    {
        $organiser = $request->attributes->get('organiser');
        $event     = Event::where('id', $eventId)->where('organiser_id', $organiser->id)->firstOrFail();
        $tier      = TicketTier::where('id', $id)->where('event_id', $event->id)->firstOrFail();

        // Prevent deletion if tickets sold
        if ($tier->sold_quantity > 0) {
            return back()->withErrors(['tier' => 'Cannot delete a tier that has sold tickets. Deactivate it instead.']);
        }

        $tier->delete();
        $this->syncEventCapacity($event);

        return back()->with('success', 'Tier deleted.');
    }

    private function validateTier(Request $request): array
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'price'          => 'required|numeric|min:0',
            'total_quantity' => 'required|integer|min:1|max:100000',
            'min_per_order'  => 'required|integer|min:1',
            'max_per_order'  => 'required|integer|min:1|max:20',
            'is_active'      => 'boolean',
            'sort_order'     => 'integer|min:0',
        ], [
            'min_per_order.required' => 'Minimum per order is required.',
            'min_per_order.min' => 'Minimum per order must be at least 1.',
            'max_per_order.required' => 'Max per order is required.',
            'max_per_order.min' => 'Max per order is invalid.',
            'max_per_order.max' => 'Max per order must be maximum 20.',
        ]);

        if (!(bool) ticketly_setting('allow_free_events', true) && (float) $validated['price'] <= 0) {
            throw ValidationException::withMessages([
                'price' => 'Free ticket tiers are disabled by the admin.',
            ]);
        }

        return $validated;
    }

    private function syncEventCapacity(Event $event): void
    {
        $total = $event->ticketTiers()->sum('total_quantity');
        $event->update(['total_capacity' => $total]);
    }
}
