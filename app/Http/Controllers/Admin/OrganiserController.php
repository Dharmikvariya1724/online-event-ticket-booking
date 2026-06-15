<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OrganiserApproved;
use App\Mail\OrganiserRejected;

class OrganiserController extends Controller
{
    public function index(Request $request)
    {
        $query = Organiser::query();

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', false)->whereNull('rejected_at');
            } elseif ($request->status === 'rejected') {
                $query->whereNotNull('rejected_at');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('account_status')) {
            if ($request->account_status === 'active') {
                $query->where('is_suspended', false);
            } elseif ($request->account_status === 'suspended') {
                $query->where('is_suspended', true);
            }
        } elseif ($request->filled('suspended')) {
            $query->where('is_suspended', (bool) $request->boolean('suspended'));
        }

        $organisers = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.organisers.index', compact('organisers'));
    }

    public function show(int $id)
    {
        $organiser = Organiser::findOrFail($id);

        $events = Event::where('organiser_id', $organiser->id)
            ->orderByDesc('created_at')
            ->get();

        $eventIds = $events->pluck('id');
        $revenue = Booking::whereIn('event_id', $eventIds)
            ->whereIn('status', ['paid', 'partially_refunded'])
            ->sum('total');

        return view('admin.organisers.show', compact('organiser', 'events', 'revenue'));
    }

    public function approve(Request $request, int $id)
    {
        $admin = $request->attributes->get('admin');
        $organiser = Organiser::findOrFail($id);

        $organiser->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by_admin_id' => $admin?->id,
            'rejected_at' => null,
            'rejection_reason' => null,
            'rejected_by_admin_id' => null,
        ]);

        try {
            Mail::to($organiser->email)->send(new OrganiserApproved($organiser));
            EmailLog::logSent($organiser->email, 'Organiser approved', 'organiser_approved', $organiser);
        } catch (\Exception $e) {
            Log::error('[Admin] Organiser approval email failed: ' . $e->getMessage(), [
                'organiser_id' => $organiser->id,
            ]);
            EmailLog::logFailed($organiser->email, 'Organiser approved', $e->getMessage(), 'organiser_approved', $organiser);
        }

        return back()->with('success', 'Organiser approved.');
    }

    public function reject(Request $request, int $id)
    {
        $admin = $request->attributes->get('admin');
        $organiser = Organiser::findOrFail($id);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ]);

        $organiser->update([
            'is_approved' => false,
            'approved_at' => null,
            'approved_by_admin_id' => null,
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
            'rejected_by_admin_id' => $admin?->id,
        ]);

        try {
            Mail::to($organiser->email)->send(new OrganiserRejected($organiser, $validated['rejection_reason']));
            EmailLog::logSent($organiser->email, 'Organiser rejected', 'organiser_rejected', $organiser, ['reason' => $validated['rejection_reason']]);
        } catch (\Exception $e) {
            Log::error('[Admin] Organiser rejection email failed: ' . $e->getMessage(), [
                'organiser_id' => $organiser->id,
            ]);
            EmailLog::logFailed($organiser->email, 'Organiser rejected', $e->getMessage(), 'organiser_rejected', $organiser, ['reason' => $validated['rejection_reason']]);
        }

        return back()->with('success', 'Organiser rejected.');
    }

    public function suspend(Request $request, int $id)
    {
        $organiser = Organiser::findOrFail($id);
        $organiser->update([
            'is_suspended' => true,
            'suspended_at' => now(),
        ]);

        return back()->with('success', 'Organiser suspended.');
    }

    public function activate(Request $request, int $id)
    {
        $organiser = Organiser::findOrFail($id);
        $organiser->update([
            'is_suspended' => false,
            'suspended_at' => null,
        ]);

        return back()->with('success', 'Organiser reactivated.');
    }
}
