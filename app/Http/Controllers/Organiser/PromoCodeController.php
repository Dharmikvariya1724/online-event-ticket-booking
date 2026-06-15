<?php

namespace App\Http\Controllers\Organiser;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PromoCode;
use App\Services\ServiceFeeCalculator;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function index(Request $request)
    {
        $organiser = $request->attributes->get('organiser');

        // Public AJAX promo validation (no organiser session needed)
        if (!$organiser) {
            return $this->validate($request);
        }

        $promos = PromoCode::withTrashed()
            ->where('organiser_id', $organiser->id)
            ->with('event')
            ->orderByDesc('created_at')
            ->paginate(20);

        $events = Event::where('organiser_id', $organiser->id)->orderBy('title')->get();

        return view('organiser.promos.index', compact('organiser', 'promos', 'events'));
    }

    public function create(Request $request)
    {
        $organiser = $request->attributes->get('organiser');
        $events    = Event::where('organiser_id', $organiser->id)->orderBy('title')->get();

        return view('organiser.promos.create', compact('organiser', 'events'));
    }

    public function store(Request $request)
    {
        $organiser = $request->attributes->get('organiser');

        $validated = $this->validatePromo($request, $organiser->id);
        $validated = $this->sanitizePromoPayload($validated, $organiser->id);
        $validated['organiser_id'] = $organiser->id;

        PromoCode::create($validated);

        return redirect()->route('organiser.promos.index')
            ->with('success', 'Promo code ' . $validated['code'] . ' created.');
    }

    public function show(Request $request, int $id)
    {
        $organiser = $request->attributes->get('organiser');
        $promo     = PromoCode::withTrashed()
            ->where('id', $id)
            ->where('organiser_id', $organiser->id)
            ->with(['event', 'bookings'])
            ->firstOrFail();

        return view('organiser.promos.show', compact('organiser', 'promo'));
    }

    public function edit(Request $request, int $id)
    {
        $organiser = $request->attributes->get('organiser');
        $promo     = PromoCode::where('id', $id)
            ->where('organiser_id', $organiser->id)
            ->firstOrFail();
        $events = Event::where('organiser_id', $organiser->id)->orderBy('title')->get();

        if ($promo->trashed()) {
            return redirect()->route('organiser.promos.show', $promo->id)
                ->withErrors(['promo' => 'Deleted promo codes cannot be edited.']);
        }

        return view('organiser.promos.edit', compact('organiser', 'promo', 'events'));
    }

    public function update(Request $request, int $id)
    {
        $organiser = $request->attributes->get('organiser');
        $promo     = PromoCode::where('id', $id)
            ->where('organiser_id', $organiser->id)
            ->firstOrFail();

        if ($promo->trashed()) {
            return redirect()->route('organiser.promos.show', $promo->id)
                ->withErrors(['promo' => 'Deleted promo codes cannot be edited.']);
        }

        $validated = $this->validatePromo($request, $organiser->id, $promo->id);
        $validated = $this->sanitizePromoPayload($validated, $organiser->id);

        $promo->update($validated);

        return redirect()->route('organiser.promos.edit', $promo->id)
            ->with('success', 'Promo code updated.');
    }

    public function deactivate(Request $request, int $id)
    {
        $organiser = $request->attributes->get('organiser');
        $promo     = PromoCode::where('id', $id)
            ->where('organiser_id', $organiser->id)
            ->firstOrFail();

        if ($promo->trashed()) {
            return back()->withErrors(['promo' => 'This promo code is already deleted.']);
        }

        $promo->update(['is_active' => false]);

        return back()->with('success', 'Promo code deactivated.');
    }

    public function activate(Request $request, int $id)
    {
        $organiser = $request->attributes->get('organiser');
        $promo     = PromoCode::where('id', $id)
            ->where('organiser_id', $organiser->id)
            ->firstOrFail();

        if ($promo->trashed()) {
            return back()->withErrors(['promo' => 'Deleted promo codes cannot be activated.']);
        }

        $promo->update(['is_active' => true]);

        return back()->with('success', 'Promo code activated.');
    }

    public function destroy(Request $request, int $id)
    {
        $organiser = $request->attributes->get('organiser');
        $promo     = PromoCode::withTrashed()
            ->where('id', $id)
            ->where('organiser_id', $organiser->id)
            ->firstOrFail();

        if ($this->isUsedPromo($promo)) {
            if (!$promo->trashed()) {
                $promo->delete();
            }

            $promo->is_active = false;
            $promo->save();

            return redirect()->route('organiser.promos.index')
                ->with('success', 'Used promo code was soft-deleted to preserve booking history.');
        }

        $promo->forceDelete();

        return redirect()->route('organiser.promos.index')
            ->with('success', 'Promo code deleted permanently.');
    }

    public function validate(Request $request)
    {
        $request->validate([
            'code'     => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'event_id' => 'nullable|integer',
        ]);

        $promo = null;

        if ($request->filled('event_id')) {
            $event = Event::select('id', 'organiser_id')->find($request->event_id);
            $resolvedPromo = PromoCode::resolveForEvent($event, (string) $request->code);

            if ($resolvedPromo['message']) {
                return response()->json([
                    'valid' => false,
                    'message' => $resolvedPromo['message'],
                ]);
            }

            $promo = $resolvedPromo['promo'];
        } else {
            $promo = PromoCode::query()
                ->whereRaw('LOWER(code) = ?', [strtolower(trim((string) $request->code))])
                ->where('is_active', true)
                ->first();
        }

        if (!$promo || !$promo->isValid()) {
            return response()->json([
                'valid'   => false,
                'message' => 'This promo code is invalid, expired, or has reached its usage limit.',
            ]);
        }

        $pricing = ServiceFeeCalculator::totalForPromo((float) $request->subtotal, $promo);

        return response()->json([
            'valid'    => true,
            'code'     => $promo->code,
            'type'     => $promo->type,
            'value'    => $promo->value,
            'gross_total' => $pricing['gross_total'],
            'discount' => $pricing['discount'],
            'message'  => $promo->type === 'percentage'
                ? number_format($promo->value, 0) . '% discount applied - saving ' . number_format($pricing['discount'], 2)
                : number_format($pricing['discount'], 2) . ' discount applied',
        ]);
    }

    private function validatePromo(Request $request, int $organiserId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code'         => [
                'required',
                'string',
                'alpha_num',
                'max:30',
                function (string $attribute, mixed $value, \Closure $fail) use ($organiserId, $ignoreId): void {
                    if (PromoCode::codeExistsForOrganiser($organiserId, (string) $value, $ignoreId)) {
                        $fail('This promo code already exists for your account.');
                    }
                },
            ],
            'type'         => 'required|in:percentage,fixed',
            'value'        => 'required|numeric|min:0.01',
            'max_discount' => 'nullable|numeric|min:0.01',
            'max_uses'     => 'nullable|integer|min:1',
            'expires_at'   => 'nullable|date|after:today',
            'event_id'     => 'nullable|exists:events,id',
            'is_active'    => 'boolean',
        ]);
    }

    private function sanitizePromoPayload(array $validated, int $organiserId): array
    {
        if (!empty($validated['event_id'])) {
            $event = Event::where('id', $validated['event_id'])
                ->where('organiser_id', $organiserId)
                ->first();

            if (!$event) {
                unset($validated['event_id']);
            }
        }

        $validated['code'] = strtoupper($validated['code']);

        return $validated;
    }

    private function isUsedPromo(PromoCode $promo): bool
    {
        return (int) $promo->used_count > 0 || $promo->bookings()->exists();
    }
}
