<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmed;
use App\Mail\EventApproved;
use App\Mail\EventRejected;
use App\Mail\EventReminder;
use App\Mail\AdminNewOrganiser;
use App\Mail\OrganiserApproved;
use App\Mail\OrganiserDailySummary;
use App\Mail\OrganiserRejected;
use App\Mail\PaymentFailed;
use App\Mail\RefundConfirmed;
use App\Models\Booking;
use App\Models\EmailLog;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('to', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('mailable', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.emails.index', compact('logs'));
    }

    public function retry(Request $request, int $id)
    {
        $log = EmailLog::findOrFail($id);

        try {
            $this->resendEmail($log);
            $log->update([
                'status' => 'sent',
                'error' => null,
                'sent_at' => now(),
            ]);

            return back()->with('success', 'Email resent successfully.');
        } catch (\Throwable $e) {
            Log::error('[Admin] Email resend failed: ' . $e->getMessage(), ['log_id' => $log->id]);
            return back()->withErrors(['email' => 'Email resend failed: ' . $e->getMessage()]);
        }
    }

    private function resendEmail(EmailLog $log): void
    {
        $meta = $log->meta ?? [];

        switch ($log->mailable) {
            case 'booking_confirmed':
                $booking = Booking::findOrFail($log->context_id);
                Mail::to($log->to)->send(new BookingConfirmed($booking));
                break;
            case 'refund_confirmed':
                $booking = Booking::findOrFail($log->context_id);
                $amount = (float) ($meta['refund_amount'] ?? 0);
                Mail::to($log->to)->send(new RefundConfirmed($booking, $amount));
                break;
            case 'payment_failed':
                $reservation = Reservation::findOrFail($log->context_id);
                $error = (string) ($meta['error'] ?? 'Payment failed.');
                Mail::to($log->to)->send(new PaymentFailed($reservation, $error));
                break;
            case 'event_reminder':
                $booking = Booking::findOrFail($log->context_id);
                $window = (string) ($meta['window'] ?? '24h');
                Mail::to($log->to)->send(new EventReminder($booking, $window));
                break;
            case 'organiser_daily_summary':
                $organiser = Organiser::findOrFail($log->context_id);
                $tickets = (int) ($meta['tickets_sold'] ?? 0);
                $date = (string) ($meta['date'] ?? now()->toDateString());
                $detailsUrl = (string) ($meta['details_url'] ?? route('organiser.orders.index'));
                Mail::to($log->to)->send(new OrganiserDailySummary($organiser, $tickets, $date, $detailsUrl));
                break;
            case 'event_cancelled':
                $booking = Booking::findOrFail($log->context_id);
                $reason = (string) ($meta['reason'] ?? 'Event cancelled');
                Mail::send('emails.event-cancelled', [
                    'booking' => $booking,
                    'reason' => $reason,
                ], function ($m) use ($log, $booking) {
                    $m->to($log->to)->subject('Event Cancelled - ' . $booking->event->title);
                });
                break;
            case 'organiser_approved':
                $organiser = Organiser::findOrFail($log->context_id);
                Mail::to($log->to)->send(new OrganiserApproved($organiser));
                break;
            case 'organiser_rejected':
                $organiser = Organiser::findOrFail($log->context_id);
                $reason = (string) ($meta['reason'] ?? '');
                Mail::to($log->to)->send(new OrganiserRejected($organiser, $reason));
                break;
            case 'event_approved':
                $event = Event::findOrFail($log->context_id);
                Mail::to($log->to)->send(new EventApproved($event));
                break;
            case 'event_rejected':
                $event = Event::findOrFail($log->context_id);
                $reason = (string) ($meta['reason'] ?? '');
                Mail::to($log->to)->send(new EventRejected($event, $reason));
                break;
            case 'admin_new_organiser':
                $organiser = Organiser::findOrFail($log->context_id);
                Mail::to($log->to)->send(new AdminNewOrganiser($organiser));
                break;
            case 'organiser_registration_pending':
                $organiser = Organiser::findOrFail($log->context_id);
                Mail::send('emails.organiser-registration-pending', [
                    'organiser' => $organiser,
                ], function ($message) use ($organiser) {
                    $message->to($organiser->email)
                        ->subject('Your Ticketly organiser registration is pending approval');
                });
                break;
            case 'organiser_password_reset':
                $organiser = Organiser::findOrFail($log->context_id);
                $token = $organiser->generatePasswordResetToken();
                $resetLink = route('organiser.password.reset.form', ['token' => $token, 'email' => $organiser->email]);
                Mail::send('emails.organiser-password-reset', [
                    'organiser' => $organiser,
                    'resetLink' => $resetLink,
                ], function ($message) use ($organiser) {
                    $message->to($organiser->email)
                        ->subject('Reset Your Ticketly Organiser Password');
                });
                break;
            default:
                throw new \RuntimeException('This email cannot be retried.');
        }
    }
}
