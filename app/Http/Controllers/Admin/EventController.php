<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendEventCancellationNotification;
use App\Mail\EventApproved;
use App\Mail\EventRejected;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EmailLog;
use App\Models\Organiser;
use App\Support\EventValidationRules;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function __construct(private readonly RefundService $refundService)
    {}

    public function index(Request $request)
    {
        $paidStatuses = ['paid', 'partially_refunded'];
        $query = Event::with('organiser')
            ->withSum(['bookingItems as sold_tickets' => fn($q) => $q->whereHas('booking', fn($b) => $b->whereIn('status', $paidStatuses))], 'quantity')
            ->withMin('ticketTiers as min_price', 'price');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('organiser_id')) {
            $query->where('organiser_id', $request->organiser_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('venue_name', 'like', "%{$search}%");
            });
        }

        $events = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $organisers = Organiser::orderBy('name')->get(['id', 'name']);

        return view('admin.events.index', compact('events', 'organisers'));
    }

    public function show(int $id)
    {
        $event = Event::with(['organiser', 'ticketTiers', 'bookings', 'sponsorships'])
            ->withSum(['bookingItems as sold_tickets' => fn($q) => $q->whereHas('booking', fn($b) => $b->whereIn('status', ['paid', 'partially_refunded']))], 'quantity')
            ->withSum(['bookings as total_revenue' => fn($q) => $q->whereIn('status', ['paid', 'partially_refunded'])], 'total')
            ->findOrFail($id);

        return view('admin.events.show', compact('event'));
    }

    public function edit(int $id)
    {
        $event = Event::findOrFail($id);
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, int $id)
    {
        $event = Event::findOrFail($id);
        $validated = $this->validateEvent($request, $event->id);
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('banner')) {
            $this->deleteBannerFile($event->banner);
            $validated['banner'] = $this->storeBannerFile($request->file('banner'));
        }

        $event->update($validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $event = Event::findOrFail($id);

        $paidBookings = Booking::where('event_id', $event->id)->whereIn('status', ['paid', 'partially_refunded'])->count();
        if ($paidBookings > 0) {
            return back()->withErrors(['delete' => 'Cannot delete an event with paid bookings.']);
        }

        $this->deleteBannerFile($event->banner);
        $this->deleteAiImageFile($event->banner_image);
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted.');
    }

    public function approve(Request $request, int $id)
    {
        $admin = $request->attributes->get('admin');
        $event = Event::with('organiser')->findOrFail($id);

        $event->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by_admin_id' => $admin?->id,
            'rejected_at' => null,
            'rejection_reason' => null,
            'rejected_by_admin_id' => null,
        ]);

        try {
            Mail::to($event->organiser->email)->send(new EventApproved($event));
            EmailLog::logSent($event->organiser->email, 'Event approved', 'event_approved', $event);
        } catch (\Exception $e) {
            Log::error('[Admin] Event approval email failed: ' . $e->getMessage(), [
                'event_id' => $event->id,
            ]);
            EmailLog::logFailed($event->organiser->email, 'Event approved', $e->getMessage(), 'event_approved', $event);
        }

        return back()->with('success', 'Event approved.');
    }

    public function reject(Request $request, int $id)
    {
        $admin = $request->attributes->get('admin');
        $event = Event::with('organiser')->findOrFail($id);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ]);

        $event->update([
            'approval_status' => 'rejected',
            'approved_at' => null,
            'approved_by_admin_id' => null,
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
            'rejected_by_admin_id' => $admin?->id,
        ]);

        try {
            Mail::to($event->organiser->email)->send(new EventRejected($event, $validated['rejection_reason']));
            EmailLog::logSent($event->organiser->email, 'Event rejected', 'event_rejected', $event, ['reason' => $validated['rejection_reason']]);
        } catch (\Exception $e) {
            Log::error('[Admin] Event rejection email failed: ' . $e->getMessage(), [
                'event_id' => $event->id,
            ]);
            EmailLog::logFailed($event->organiser->email, 'Event rejected', $e->getMessage(), 'event_rejected', $event, ['reason' => $validated['rejection_reason']]);
        }

        return back()->with('success', 'Event rejected.');
    }

    public function cancel(Request $request, int $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:1000',
        ]);

        $bookings = Booking::where('event_id', $event->id)
            ->whereIn('status', ['paid', 'partially_refunded'])
            ->get();

        DB::transaction(function () use ($event, $validated) {
            $event->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['cancellation_reason'],
            ]);
        });

        foreach ($bookings as $booking) {
            dispatch(new SendEventCancellationNotification($booking, $validated['cancellation_reason']));
        }

        $refundReason = 'Event cancelled by admin: ' . $validated['cancellation_reason'];
        $refunds = $this->refundService->processBulkCancellationRefunds($event->id, $refundReason);

        Log::info('[Admin] Event cancelled and refunds processed', [
            'event_id' => $event->id,
            'refunds_processed' => $refunds['processed'] ?? 0,
            'refunds_failed' => $refunds['failed'] ?? 0,
        ]);

        $message = 'Event cancelled. Customers have been notified and refunds initiated.';
        if (($refunds['failed'] ?? 0) > 0) {
            $message .= ' Some refunds failed; check logs for details.';
        }

        return redirect()->route('admin.events.index')->with('success', $message);
    }

    private function validateEvent(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate(
            EventValidationRules::rules($ignoreId !== null),
            EventValidationRules::messages()
        );
    }

    private function storeBannerFile(\Illuminate\Http\UploadedFile $file): string
    {
        $directory = $this->getUploadsRoot() . DIRECTORY_SEPARATOR . 'events';
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/events/' . $filename;
    }

    private function deleteBannerFile(?string $banner): void
    {
        if (!$banner) {
            return;
        }

        $path = $this->resolveBannerPath($banner);
        if ($path && File::exists($path)) {
            File::delete($path);
        }
    }

    private function deleteAiImageFile(?string $aiImage): void
    {
        if (!$aiImage) {
            return;
        }

        $path = $this->resolveAiImagePath($aiImage);
        if ($path && File::exists($path)) {
            File::delete($path);
        }
    }

    private function resolveBannerPath(string $banner): ?string
    {
        if (str_starts_with($banner, 'http://') || str_starts_with($banner, 'https://')) {
            return null;
        }

        if (str_starts_with($banner, 'uploads/')) {
            $basePath = base_path($banner);
            if (File::exists($basePath)) {
                return $basePath;
            }

            return public_path($banner);
        }

        $fallback = 'uploads/events/' . basename($banner);
        $basePath = base_path($fallback);
        if (File::exists($basePath)) {
            return $basePath;
        }

        return public_path($fallback);
    }

    private function resolveAiImagePath(string $aiImage): ?string
    {
        if (str_starts_with($aiImage, 'http://') || str_starts_with($aiImage, 'https://')) {
            return null;
        }

        if (str_starts_with($aiImage, 'ai_image/')) {
            return public_path($aiImage);
        }

        return public_path('ai_image/' . basename($aiImage));
    }

    private function getUploadsRoot(): string
    {
        $baseUploads = base_path('uploads');
        if (File::exists($baseUploads)) {
            return $baseUploads;
        }

        return public_path('uploads');
    }
}
