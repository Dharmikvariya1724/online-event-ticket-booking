<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\TicketScanController;
use App\Http\Controllers\Organiser\AuthController as OrganiserAuthController;
use App\Http\Controllers\Organiser\DashboardController;
use App\Http\Controllers\Organiser\AnalyticsController;
use App\Http\Controllers\Organiser\EventController as OrganiserEventController;
use App\Http\Controllers\Organiser\TicketTierController;
use App\Http\Controllers\Organiser\OrderController as OrganiserOrderController;
use App\Http\Controllers\Organiser\PayoutController;
use App\Http\Controllers\Organiser\PromoCodeController;
use App\Http\Controllers\Organiser\ProfileController;
use App\Http\Controllers\Organiser\ScanController;
use App\Http\Controllers\Organiser\SponsorshipController;
use App\Http\Controllers\Organiser\StripeConnectController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\OrganiserController as AdminOrganiserController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\EmailLogController as AdminEmailLogController;
// ── Public ──────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
Route::view('/terms-of-service', 'legal.terms')->name('terms');
Route::view('/privacy-policy', 'legal.privacy')->name('privacy');
Route::get('/tickets/scan', [TicketScanController::class, 'show'])->name('tickets.scan.show');
Route::get('/organiser/scan', [ScanController::class, 'index'])->name('organiser.scan.index');

// ── Reservation & Checkout ───────────────────────────────────────────────────
Route::post('/reserve', [ReservationController::class, 'store'])->name('reservation.store');
Route::delete('/reserve/{token}', [ReservationController::class, 'release'])->name('reservation.release');
Route::get('/checkout/{token}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{token}/promo', [CheckoutController::class, 'applyPromo'])->name('checkout.promo');
Route::post('/checkout/{token}/intent', [CheckoutController::class, 'createIntent'])->name('checkout.intent');
Route::get('/checkout/{token}/poll', [CheckoutController::class, 'pollStatus'])->name('checkout.poll');
Route::get('/checkout/{token}/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/{token}/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
Route::get('/bookings/{reference}/ticket.pdf', [BookingController::class, 'ticketPdf'])->name('booking.ticket.pdf');
Route::get('/bookings/{reference}', [BookingController::class, 'show'])->name('booking.show');

// ── Promo (public AJAX) ──────────────────────────────────────────────────────
Route::post('/promo/validate', [PromoCodeController::class, 'validate'])->name('promo.validate');

// ── Stripe Webhook ───────────────────────────────────────────────────────────
Route::post('/webhooks/stripe', [WebhookController::class, 'handle'])->name('webhooks.stripe');

// ── Organiser Auth (public) ───────────────────────────────────────────────────
Route::get('/organiser/register', [OrganiserAuthController::class, 'showRegister'])->name('organiser.register');
Route::post('/organiser/register', [OrganiserAuthController::class, 'register']);
Route::get('/organiser/login', [OrganiserAuthController::class, 'showLogin'])->name('organiser.login');
Route::post('/organiser/login', [OrganiserAuthController::class, 'login']);
Route::post('/organiser/logout', [OrganiserAuthController::class, 'logout'])->name('organiser.logout');
Route::get('/organiser/pending', [OrganiserAuthController::class, 'pending'])->name('organiser.pending');
Route::get('/organiser/forgot-password', [OrganiserAuthController::class, 'showForgotPassword'])->name('organiser.password.request');
Route::post('/organiser/forgot-password', [OrganiserAuthController::class, 'sendResetLink'])->name('organiser.password.email');
Route::get('/organiser/reset-password/{token}', [OrganiserAuthController::class, 'showResetForm'])->name('organiser.password.reset.form');
Route::post('/organiser/reset-password', [OrganiserAuthController::class, 'resetPassword'])->name('organiser.password.reset');

// ── Admin Auth ─────────────────────────────────────────────────────────────
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// ── Admin Protected ─────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['is_admin'])->group(function () {
    Route::get('/', fn() => redirect()->route('admin.dashboard'))->name('home');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/run-tasks', [AdminDashboardController::class, 'runScheduledTasks'])->name('dashboard.run-tasks');

    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{id}', [AdminCustomerController::class, 'show'])->name('customers.show');
    Route::post('/customers/{id}/suspend', [AdminCustomerController::class, 'suspend'])->name('customers.suspend');
    Route::post('/customers/{id}/activate', [AdminCustomerController::class, 'activate'])->name('customers.activate');
    Route::delete('/customers/{id}', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/organisers', [AdminOrganiserController::class, 'index'])->name('organisers.index');
    Route::get('/organisers/{id}', [AdminOrganiserController::class, 'show'])->name('organisers.show');
    Route::post('/organisers/{id}/approve', [AdminOrganiserController::class, 'approve'])->name('organisers.approve');
    Route::post('/organisers/{id}/reject', [AdminOrganiserController::class, 'reject'])->name('organisers.reject');
    Route::post('/organisers/{id}/suspend', [AdminOrganiserController::class, 'suspend'])->name('organisers.suspend');
    Route::post('/organisers/{id}/activate', [AdminOrganiserController::class, 'activate'])->name('organisers.activate');

    Route::get('/events', [AdminEventController::class, 'index'])->name('events.index');
    Route::get('/events/{id}', [AdminEventController::class, 'show'])->name('events.show');
    Route::get('/events/{id}/edit', [AdminEventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{id}', [AdminEventController::class, 'update'])->name('events.update');
    Route::delete('/events/{id}', [AdminEventController::class, 'destroy'])->name('events.destroy');
    Route::post('/events/{id}/approve', [AdminEventController::class, 'approve'])->name('events.approve');
    Route::post('/events/{id}/reject', [AdminEventController::class, 'reject'])->name('events.reject');
    Route::post('/events/{id}/cancel', [AdminEventController::class, 'cancel'])->name('events.cancel');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/refund', [AdminOrderController::class, 'refund'])->name('orders.refund');
    Route::post('/orders/{id}/partial-cancel', [AdminOrderController::class, 'partialCancel'])->name('orders.partial-cancel');

    Route::get('/payouts', [AdminPayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/{organiserId}/trigger', [AdminPayoutController::class, 'trigger'])->name('payouts.trigger');

    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');

    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

    Route::get('/emails', [AdminEmailLogController::class, 'index'])->name('emails.index');
    Route::post('/emails/{id}/retry', [AdminEmailLogController::class, 'retry'])->name('emails.retry');
});

// ── Organiser Protected ───────────────────────────────────────────────────────
Route::middleware(['organiser.auth'])->group(function () {

    Route::get('/organiser/dashboard', [DashboardController::class, 'index'])->name('organiser.dashboard');
    Route::get('/organiser/analytics', [AnalyticsController::class, 'index'])->name('organiser.analytics.index');

    Route::get('/organiser/profile', [ProfileController::class, 'show'])->name('organiser.profile.show');
    Route::get('/organiser/profile/edit', [ProfileController::class, 'edit'])->name('organiser.profile.edit');
    Route::put('/organiser/profile', [ProfileController::class, 'update'])->name('organiser.profile.update');
    Route::post('/organiser/profile/password', [ProfileController::class, 'updatePassword'])->name('organiser.profile.password');
    Route::delete('/organiser/profile', [ProfileController::class, 'destroy'])->name('organiser.profile.destroy');

    Route::get('/organiser/events', [OrganiserEventController::class, 'index'])->name('organiser.events.index');
    Route::get('/organiser/events/create', [OrganiserEventController::class, 'create'])->name('organiser.events.create');
    Route::post('/organiser/events/poster/autofill', [OrganiserEventController::class, 'parsePoster'])->name('organiser.events.poster.parse');
    Route::get('/organiser/events/{id}', [OrganiserEventController::class, 'show'])->name('organiser.events.show');
    Route::post('/organiser/events', [OrganiserEventController::class, 'store'])->name('organiser.events.store');
    Route::get('/organiser/events/{id}/edit', [OrganiserEventController::class, 'edit'])->name('organiser.events.edit');
    Route::put('/organiser/events/{id}', [OrganiserEventController::class, 'update'])->name('organiser.events.update');
    Route::delete('/organiser/events/{id}', [OrganiserEventController::class, 'destroy'])->name('organiser.events.destroy');
    Route::post('/organiser/events/{id}/status', [OrganiserEventController::class, 'updateStatus'])->name('organiser.events.status');

    Route::get('/organiser/events/{eventId}/tiers', [TicketTierController::class, 'index'])->name('organiser.tiers.index');
    Route::get('/organiser/events/{eventId}/tiers/create', [TicketTierController::class, 'create'])->name('organiser.tiers.create');
    Route::post('/organiser/events/{eventId}/tiers', [TicketTierController::class, 'store'])->name('organiser.tiers.store');
    Route::get('/organiser/events/{eventId}/tiers/{id}/edit', [TicketTierController::class, 'edit'])->name('organiser.tiers.edit');
    Route::put('/organiser/events/{eventId}/tiers/{id}', [TicketTierController::class, 'update'])->name('organiser.tiers.update');
    Route::delete('/organiser/events/{eventId}/tiers/{id}', [TicketTierController::class, 'destroy'])->name('organiser.tiers.destroy');

    Route::get('/organiser/events/{eventId}/sponsorships', [SponsorshipController::class, 'index'])->name('organiser.sponsorships.index');
    Route::get('/organiser/events/{eventId}/sponsorships/create', [SponsorshipController::class, 'create'])->name('organiser.sponsorships.create');
    Route::post('/organiser/events/{eventId}/sponsorships', [SponsorshipController::class, 'store'])->name('organiser.sponsorships.store');
    Route::get('/organiser/events/{eventId}/sponsorships/{id}/edit', [SponsorshipController::class, 'edit'])->name('organiser.sponsorships.edit');
    Route::put('/organiser/events/{eventId}/sponsorships/{id}', [SponsorshipController::class, 'update'])->name('organiser.sponsorships.update');
    Route::delete('/organiser/events/{eventId}/sponsorships/{id}', [SponsorshipController::class, 'destroy'])->name('organiser.sponsorships.destroy');

    Route::get('/organiser/orders', [OrganiserOrderController::class, 'index'])->name('organiser.orders.index');
    Route::get('/organiser/orders/{id}', [OrganiserOrderController::class, 'show'])->name('organiser.orders.show');
    // Route::post('/organiser/orders/{id}/refund', [OrganiserOrderController::class, 'refund'])->name('organiser.orders.refund');
    Route::get('/organiser/payouts', [PayoutController::class, 'index'])->name('organiser.payouts.index');
    Route::get('/organiser/stripe/connect', [StripeConnectController::class, 'connect'])->name('organiser.stripe.connect');
    Route::get('/organiser/stripe/return', [StripeConnectController::class, 'return'])->name('organiser.stripe.return');
    Route::get('/organiser/stripe/refresh', [StripeConnectController::class, 'refresh'])->name('organiser.stripe.refresh');

    Route::get('/organiser/promo-codes', [PromoCodeController::class, 'index'])->name('organiser.promos.index');
    Route::get('/organiser/promo-codes/create', [PromoCodeController::class, 'create'])->name('organiser.promos.create');
    Route::post('/organiser/promo-codes', [PromoCodeController::class, 'store'])->name('organiser.promos.store');
    Route::get('/organiser/promo-codes/{id}', [PromoCodeController::class, 'show'])->name('organiser.promos.show');
    Route::delete('/organiser/promo-codes/{id}', [PromoCodeController::class, 'destroy'])->name('organiser.promos.destroy');
    Route::post('/organiser/promo-codes/{id}/activate', [PromoCodeController::class, 'activate'])->name('organiser.promos.activate');
    Route::post('/organiser/promo-codes/{id}/deactivate', [PromoCodeController::class, 'deactivate'])->name('organiser.promos.deactivate');
    Route::get('/organiser/promo-codes/{id}/edit', [PromoCodeController::class, 'edit'])->name('organiser.promos.edit');
    Route::put('/organiser/promo-codes/{id}', [PromoCodeController::class, 'update'])->name('organiser.promos.update');
});
