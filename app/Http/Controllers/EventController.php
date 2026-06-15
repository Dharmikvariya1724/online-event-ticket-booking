<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : null;
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : null;

        if ($dateFrom && $dateTo && $dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
            $request->merge([
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ]);
        }

        $query = Event::with(['ticketTiers', 'organiser'])
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('approval_status')
                  ->orWhere('approval_status', 'approved');
            })
            ->where('starts_at', '>', now());

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('short_description', 'like', "%{$s}%")
                  ->orWhere('venue_name', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%");
            });
        }
        if ($request->filled('date')) {
            $query->whereDate('starts_at', $request->date); // backward compatibility
        }
        if ($dateFrom) {
            $query->where('starts_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('starts_at', '<=', $dateTo);
        }
        if ($request->filled('price') && $request->price === 'free') {
            $query->whereHas('ticketTiers', fn($q) => $q->where('price', 0)->where('is_active', true));
        }
        if ($request->filled('price_min') || $request->filled('price_max')) {
            $priceMin = is_numeric($request->price_min) ? (float) $request->price_min : 0;
            $priceMax = is_numeric($request->price_max) ? (float) $request->price_max : null;

            $query->whereHas('ticketTiers', function ($q) use ($priceMin, $priceMax) {
                $q->where('is_active', true)
                    ->where('price', '>=', $priceMin);

                if ($priceMax !== null) {
                    $q->where('price', '<=', $priceMax);
                }
            });
        }

        $query->orderBy('starts_at');

        $events = $query->paginate(12)->withQueryString();

        $categoryCounts = Event::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('approval_status')
                  ->orWhere('approval_status', 'approved');
            })
            ->where('starts_at', '>', now())
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->pluck('count', 'category');
        $categories = collect(Event::CATEGORIES)
            ->mapWithKeys(fn($cat) => [$cat => (int) ($categoryCounts[$cat] ?? 0)]);

        $cities = Event::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('approval_status')
                  ->orWhere('approval_status', 'approved');
            })
            ->where('starts_at', '>', now())
            ->selectRaw('city, COUNT(*) as count')
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'city');

        return view('events.index', compact('events', 'categories', 'cities'));
    }

    public function show(Request $request, string $slug)
    {
        if ($this->hasTicketScanParameters($request)) {
            return redirect()->route('events.show', array_merge(
                ['slug' => $slug],
                $this->cleanEventQueryParameters($request)
            ));
        }

        $event = Event::with([
                'ticketTiers' => fn($q) => $q->where('is_active', true)->orderBy('price'),
                'organiser',
                'sponsorships',
            ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('approval_status')
                  ->orWhere('approval_status', 'approved');
            })
            ->firstOrFail();

        $activeReservation = null;
        if ($request->filled('reservation')) {
            $activeReservation = Reservation::with('items')
                ->where('token', $request->string('reservation'))
                ->where('event_id', $event->id)
                ->where('session_id', $request->session()->getId())
                ->where('status', 'pending')
                ->first();

            if ($activeReservation?->isExpired()) {
                $activeReservation = null;
            }
        }

        $selectedTicketItems = collect(old('items', []));
        if ($selectedTicketItems->isEmpty() && $activeReservation) {
            $selectedTicketItems = $activeReservation->items
                ->map(fn ($item) => [
                    'ticket_tier_id' => (int) $item->ticket_tier_id,
                    'quantity' => (int) $item->quantity,
                ]);
        }

        // Related events (same category, different event)
        $relatedEvents = Event::with('ticketTiers')
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('approval_status')
                  ->orWhere('approval_status', 'approved');
            })
            ->where('starts_at', '>', now())
            ->where('category', $event->category)
            ->where('id', '!=', $event->id)
            ->limit(3)
            ->get();

        return view('events.show', compact('event', 'relatedEvents', 'activeReservation', 'selectedTicketItems'));
    }

    private function hasTicketScanParameters(Request $request): bool
    {
        return $request->filled('ticket_uuid')
            || $request->filled('booking_reference')
            || $request->filled('reference')
            || $request->filled('data');
    }

    private function cleanEventQueryParameters(Request $request): array
    {
        return collect($request->query())
            ->except(['ticket_uuid', 'booking_reference', 'reference', 'data'])
            ->all();
    }
}
