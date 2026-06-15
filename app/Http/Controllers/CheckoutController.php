<?php

namespace App\Http\Controllers;

use App\Jobs\SendBookingConfirmation;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\PromoCode;
use App\Models\Reservation;
use App\Repositories\BookingRepository;
use App\Services\PaymentFailureNotifier;
use App\Services\ServiceFeeCalculator;
use App\Services\VisitorGeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Stripe\PaymentIntent;
use Stripe\Exception\InvalidRequestException;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly BookingRepository $bookingRepo,
        private readonly PaymentFailureNotifier $paymentFailureNotifier,
        private readonly VisitorGeoService $visitorGeoService
    ) {}

    // ── Show checkout ─────────────────────────────────────────────
    public function show(string $token)
    {
        $reservation = Reservation::with(['event', 'items.ticketTier', 'promoCode'])
            ->where('token', $token)
            ->firstOrFail();

        // Already completed → redirect to booking
        if ($reservation->status === 'completed') {
            $booking = Booking::where('reservation_id', $reservation->id)
                ->where('status', 'paid')->first();
            if ($booking) return redirect()->route('booking.show', $booking->reference);
        }

        // Expired
        if ($reservation->isExpired()) {
            if ($reservation->status === 'pending') {
                ReservationController::releaseReservation($reservation);
            }
            return redirect()->route('events.show', $reservation->event->slug)
                ->withErrors(['reservation' => 'Your 10-minute ticket hold has expired. Please select tickets again.']);
        }

        $feePct = config('ticketly.service_fee_percentage', 5);
        $portalFeePct = config('ticketly.portal_fee_percentage', 10);

        return view('checkout.show', compact('reservation', 'feePct', 'portalFeePct'));
    }

    // ── Create / update PaymentIntent ─────────────────────────────
    public function applyPromo(Request $request, string $token)
    {
        $reservation = Reservation::with(['event', 'promoCode'])
            ->where('token', $token)
            ->first();

        if (!$reservation || $reservation->status !== 'pending') {
            return response()->json([
                'error' => 'Your ticket hold is no longer active.',
                'expired' => true,
                'redirect_to' => route('events.index'),
            ], 422);
        }

        if ($reservation->isExpired()) {
            ReservationController::releaseReservation($reservation);

            return response()->json([
                'error' => 'Your 10-minute ticket hold has expired. Tickets are being released now.',
                'expired' => true,
                'redirect_to' => route('events.show', $reservation->event->slug),
            ], 422);
        }

        $validated = $request->validate([
            'promo_code' => 'nullable|string|max:100',
        ]);

        $promoCode = $this->resolveCheckoutPromo(
            $reservation,
            (string) ($validated['promo_code'] ?? ''),
            false
        );

        $pricing = $this->pricingForReservation($reservation, $promoCode);
        $this->updateReservationPricing($reservation, $pricing, $promoCode);

        return response()->json([
            'valid' => true,
            'code' => $promoCode?->code,
            'promo_type' => $promoCode?->type,
            'promo_value' => $promoCode ? (float) $promoCode->value : null,
            'discount' => $pricing['discount'],
            'gross_total' => $pricing['gross_total'],
            'portal_fee' => $pricing['portal_fee'],
            'service_fee' => $pricing['service_fee'],
            'amount' => $pricing['total'],
            'message' => $promoCode
                ? ($promoCode->type === 'percentage'
                    ? number_format($promoCode->value, 0) . '% discount applied - saving ' . number_format($pricing['discount'], 2)
                    : number_format($pricing['discount'], 2) . ' discount applied')
                : 'Promo code removed.',
        ]);
    }

    public function createIntent(Request $request, string $token)
    {
        $reservation = Reservation::with(['event', 'items.ticketTier', 'promoCode'])
            ->where('token', $token)
            ->first();

        if (!$reservation || $reservation->status !== 'pending') {
            return response()->json([
                'error'       => 'Your ticket hold is no longer active.',
                'expired'     => true,
                'redirect_to' => route('events.index'),
            ], 422);
        }

        if ($reservation->isExpired()) {
            ReservationController::releaseReservation($reservation);
            return response()->json([
                'error'       => 'Your 10-minute ticket hold has expired. Tickets are being released now.',
                'expired'     => true,
                'redirect_to' => route('events.show', $reservation->event->slug),
            ], 422);
        }

        $validated = $this->validateCheckoutContact($request);

        // Promo
        $promoCode = $this->resolveCheckoutPromo(
            $reservation,
            (string) ($validated['promo_code'] ?? ''),
            true
        );

        $pricing = $this->pricingForReservation($reservation, $promoCode);

        $connectedOrganiser = $this->connectedOrganiser($reservation);
        $platformFee = $pricing['portal_fee'] + $pricing['service_fee'];
        $countryContext = $this->resolveCountryFromRequest($request);
        $customerName = trim((string) ($validated['name'] ?? $reservation->customer_name ?? ''));
        $customerEmail = trim((string) ($validated['email'] ?? $reservation->customer_email ?? ''));
        $customerPhone = trim((string) ($validated['phone'] ?? $reservation->customer_phone ?? ''));

        if ($customerName === '') {
            $customerName = null;
        }
        if ($customerEmail === '') {
            $customerEmail = null;
        }
        if ($customerPhone === '') {
            $customerPhone = null;
        }

        $billingProfile = $this->resolveBillingProfile($request, $customerName);

        $updateData = [];
        if ($customerName !== null) {
            $updateData['customer_name'] = $customerName;
        }
        if ($customerEmail !== null) {
            $updateData['customer_email'] = $customerEmail;
        }
        if ($customerPhone !== null) {
            $updateData['customer_phone'] = $customerPhone;
        }
        $this->updateReservationPricing($reservation, $pricing, $promoCode, $updateData);

        $customerName = $reservation->customer_name ? trim((string) $reservation->customer_name) : null;
        $customerEmail = $reservation->customer_email ? trim((string) $reservation->customer_email) : null;
        $customerPhone = $reservation->customer_phone ? trim((string) $reservation->customer_phone) : null;

        if (!empty($customerEmail)) {
            $customer = Customer::where('email', $customerEmail)->first();
            if ($customer && $customer->is_suspended) {
                return response()->json([
                    'error' => 'This customer account is suspended. Please contact support.',
                ], 403);
            }
        }

        if ($pricing['total'] <= 0 && !ticketly_setting('allow_free_events', true)) {
            return response()->json([
                'error' => 'Free events are currently disabled. Please contact support.',
            ], 422);
        }

        if ($pricing['total'] <= 0) {
            $booking = $this->createBookingFromReservation($reservation, 'FREE');
            return response()->json([
                'free'     => true,
                'redirect' => route('booking.show', $booking->reference),
            ]);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $amountPence = max(5000, ServiceFeeCalculator::toPence($pricing['total']));

            $intentReceiptEmail = $customerEmail ?: null;
            $shipping = null;

            $metadata = [
                'reservation_id'    => (string) $reservation->id,
                'reservation_token' => $token,
            ];
            if ($connectedOrganiser) {
                $metadata['organiser_id'] = (string) $connectedOrganiser->id;
                $metadata['organiser_stripe_account_id'] = (string) $connectedOrganiser->stripe_account_id;
            }
            if (!empty($customerEmail)) {
                $metadata['customer_email'] = (string) $customerEmail;
            }
            if (!empty($customerName)) {
                $metadata['customer_name'] = (string) $customerName;
            }
            if (!empty($customerPhone)) {
                $metadata['customer_phone'] = (string) $customerPhone;
            }
            if (!empty($countryContext['country_code'])) {
                $metadata['ip_country_code'] = (string) $countryContext['country_code'];
            }
            if (!empty($countryContext['country'])) {
                $metadata['ip_country'] = (string) $countryContext['country'];
            }
            if (!empty($countryContext['city'])) {
                $metadata['ip_city'] = (string) $countryContext['city'];
            }
            if (!empty($countryContext['region'])) {
                $metadata['ip_region'] = (string) $countryContext['region'];
            }
            if (!empty($billingProfile['address']['country'])) {
                $metadata['billing_country_code'] = (string) $billingProfile['address']['country'];
            }
            if (!empty($billingProfile['source'])) {
                $metadata['billing_source'] = (string) $billingProfile['source'];
            }

            if ($reservation->stripe_payment_intent_id) {
                try {
                    $intent = $this->updateIntent(
                        $reservation->stripe_payment_intent_id,
                        $amountPence,
                        $metadata,
                        $intentReceiptEmail,
                        $shipping,
                        $connectedOrganiser?->stripe_account_id,
                        $platformFee
                    );
                } catch (\Exception $e) {
                    // Intent may be in non-updatable state; create fresh.
                    $intent = $this->createFreshIntent(
                        $amountPence,
                        ['email' => $intentReceiptEmail],
                        $reservation->event->title,
                        $metadata,
                        $shipping,
                        $connectedOrganiser?->stripe_account_id,
                        $platformFee
                    );
                    $reservation->update(['stripe_payment_intent_id' => $intent->id]);
                }
            } else {
                $intent = $this->createFreshIntent(
                    $amountPence,
                    ['email' => $intentReceiptEmail],
                    $reservation->event->title,
                    $metadata,
                    $shipping,
                    $connectedOrganiser?->stripe_account_id,
                    $platformFee
                );
                $reservation->update(['stripe_payment_intent_id' => $intent->id]);
            }
        } catch (\Throwable $e) {
            Log::error('[Checkout] Unable to prepare PaymentIntent', [
                'reservation_id' => $reservation->id,
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Unable to initialize card payment right now. Please try again in a moment.',
            ], 503);
        }

        return response()->json([
            'client_secret'  => $intent->client_secret,
            'intent_id'      => $intent->id,
            'amount'         => $pricing['total'],
            'discount'       => $pricing['discount'],
            'gross_total'    => $pricing['gross_total'],
            'portal_fee'     => $pricing['portal_fee'],
            'service_fee'    => $pricing['service_fee'],
            'currency'       => ticketly_currency(),
            'billing_profile' => $billingProfile,
        ]);
    }

    // ── Poll status ───────────────────────────────────────────────
    public function pollStatus(Request $request, string $token)
    {
        $reservation = Reservation::with('event')->where('token', $token)->firstOrFail();

        if ($reservation->status === 'pending' && $reservation->isExpired()) {
            ReservationController::releaseReservation($reservation);
            return response()->json([
                'status'      => 'expired',
                'message'     => 'Your 10-minute hold has expired. Tickets are being released.',
                'redirect_to' => route('events.show', $reservation->event->slug),
            ]);
        }

        if (in_array($reservation->status, ['expired', 'released'])) {
            return response()->json([
                'status'      => 'expired',
                'message'     => 'Your ticket hold is no longer active.',
                'redirect_to' => route('events.show', $reservation->event->slug),
            ]);
        }

        if ($reservation->status === 'completed') {
            $booking = Booking::where('reservation_id', $reservation->id)->where('status', 'paid')->first();
            if ($booking) {
                return response()->json([
                    'status'   => 'paid',
                    'redirect' => route('booking.show', $booking->reference),
                ]);
            }
        }

        if ($reservation->stripe_payment_intent_id) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $intent = PaymentIntent::retrieve($reservation->stripe_payment_intent_id);

                if ($intent->status === 'succeeded' && $reservation->status === 'pending') {
                    if ($reservation->isExpired()) {
                        ReservationController::releaseReservation($reservation);
                        return response()->json([
                            'status'      => 'expired',
                            'message'     => 'Your hold expired before payment completion. No payment will be taken.',
                            'redirect_to' => route('events.show', $reservation->event->slug),
                        ]);
                    }

                    $booking = $this->createBookingFromReservation($reservation, $intent->id);
                    return response()->json([
                        'status'   => 'paid',
                        'redirect' => route('booking.show', $booking->reference),
                    ]);
                }
                $failedStatuses = ['canceled', 'requires_payment_method'];
                $hasError = !empty($intent->last_payment_error?->message);
                $attempted = $request->boolean('attempt');
                $isFailedState = $intent->status === 'canceled'
                    || ($intent->status === 'requires_payment_method' && ($hasError || $attempted));

                if (in_array($intent->status, $failedStatuses, true) && $isFailedState) {
                    $errorMessage = $intent->last_payment_error?->message
                        ?? 'Your payment could not be processed.';

                    $this->paymentFailureNotifier->notify($reservation, $errorMessage);

                    return response()->json([
                        'status'  => 'failed',
                        'message' => $errorMessage,
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('[Checkout] Poll error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status'  => 'pending',
            'expires' => $reservation->secondsRemaining(),
        ]);
    }

    // ── Success return URL ────────────────────────────────────────
    public function success(string $token)
    {
        $reservation = Reservation::with('event')->where('token', $token)->firstOrFail();

        if ($reservation->status === 'completed') {
            $booking = Booking::where('reservation_id', $reservation->id)->where('status', 'paid')->first();
            if ($booking) return redirect()->route('booking.show', $booking->reference);
        }

        return view('checkout.processing', compact('reservation', 'token'));
    }

    // ── Cancel / retry ────────────────────────────────────────────
    // public function cancel(string $token)
    // {
    //     $reservation = Reservation::with('event')->where('token', $token)->first();
    //     if ($reservation && $reservation->isActive()) {
    //         return redirect()->route('checkout.show', $token)->with('payment_cancelled', true);
    //     }
    //     return redirect()->route('events.index');
    // }

    public function cancel(Request $request, string $token)
    {
        $reservation = Reservation::with('event')->where('token', $token)->first();

        if ($reservation && $reservation->isActive()) {

            $error = $request->query('error');

            return redirect()
                ->route('checkout.show', $token)
                ->with('payment_error', $error);
        }

        return redirect()->route('events.index');
    }

    // ── Public helper (used by WebhookController + poll) ──────────
    public function createBookingFromReservation(Reservation $reservation, string $intentId): Booking
    {
        $booking = $this->bookingRepo->createFromReservation($reservation, $intentId);
        SendBookingConfirmation::dispatchSync($booking);
        return $booking;
    }

    // ── Private ───────────────────────────────────────────────────
    private function createFreshIntent(
        int $amountPence,
        array $customer,
        string $eventTitle,
        array $metadata,
        ?array $shipping = null,
        ?string $stripeAccountId = null,
        float $platformFee = 0.0
    ): PaymentIntent
    {
        $payload = [
            'amount'               => $amountPence,
            'currency'             => strtolower(ticketly_currency()),
            'payment_method_types' => ['card'],
            'receipt_email'        => $customer['email'] ?? null,
            'description'          => $eventTitle,
            'metadata'             => $metadata,
        ];
        if (!empty($shipping)) {
            $payload['shipping'] = $shipping;
        }

        if (!empty($stripeAccountId)) {
            $payload['application_fee_amount'] = $this->platformFeePence($platformFee, $amountPence);
            $payload['transfer_data'] = ['destination' => $stripeAccountId];
        }

        try {
            return PaymentIntent::create($payload);
        } catch (InvalidRequestException $e) {
            if (empty($stripeAccountId)) {
                throw $e;
            }

            Log::warning('[Checkout] PaymentIntent create failed with Connect destination. Retrying without transfer_data.', [
                'error' => $e->getMessage(),
                'destination' => $stripeAccountId,
            ]);

            unset($payload['application_fee_amount'], $payload['transfer_data']);
            return PaymentIntent::create($payload);
        }
    }

    private function updateIntent(
        string $intentId,
        int $amountPence,
        array $metadata,
        ?string $customerEmail,
        ?array $shipping,
        ?string $stripeAccountId,
        float $platformFee
    ): PaymentIntent {
        $intentUpdate = [
            'amount'               => $amountPence,
            'metadata'             => $metadata,
            'payment_method_types' => ['card'],
        ];
        if (!empty($customerEmail)) {
            $intentUpdate['receipt_email'] = $customerEmail;
        }
        if (!empty($shipping)) {
            $intentUpdate['shipping'] = $shipping;
        }
        if (!empty($stripeAccountId)) {
            $intentUpdate['application_fee_amount'] = $this->platformFeePence($platformFee, $amountPence);
            $intentUpdate['transfer_data'] = ['destination' => $stripeAccountId];
        }

        try {
            return PaymentIntent::update($intentId, $intentUpdate);
        } catch (InvalidRequestException $e) {
            if (empty($stripeAccountId)) {
                throw $e;
            }

            Log::warning('[Checkout] PaymentIntent update failed with Connect destination. Retrying without transfer_data.', [
                'error' => $e->getMessage(),
                'destination' => $stripeAccountId,
            ]);

            unset($intentUpdate['application_fee_amount'], $intentUpdate['transfer_data']);
            return PaymentIntent::update($intentId, $intentUpdate);
        }
    }

    private function connectedOrganiser(Reservation $reservation): ?\App\Models\Organiser
    {
        $reservation->loadMissing('event.organiser');
        $organiser = $reservation->event?->organiser;
        if (!$organiser) return null;
        if (!$organiser->stripe_account_id) return null;
        if (!$organiser->stripe_onboarding_complete) return null;
        return $organiser;
    }

    private function platformFeePence(float $platformFee, int $amountPence): int
    {
        $feePence = ServiceFeeCalculator::toPence($platformFee);
        return min($feePence, $amountPence);
    }

    private function resolveCountryFromRequest(Request $request): array
    {
        $geo = [];
        $countryCode = $this->normalizeCountryCode(
            $request->header('cf-ipcountry')
                ?? $request->header('x-geo-country')
                ?? $request->header('x-appengine-country')
        );
        $country = $request->header('x-geo-country-name');

        if (!$countryCode || !$country) {
            try {
                $geo = $this->visitorGeoService->lookup((string) $request->ip()) ?? [];
            } catch (\Throwable $e) {
                Log::debug('[Checkout] Geo lookup failed', ['error' => $e->getMessage()]);
                $geo = [];
            }

            $countryCode = $countryCode ?: $this->normalizeCountryCode($geo['country_code'] ?? null);
            $country = $country ?: ($geo['country'] ?? null);
        }

        return [
            'country_code' => $countryCode ?: null,
            'country' => $country ?: null,
            'city' => $geo['city'] ?? null,
            'region' => $geo['region'] ?? null,
        ];
    }

    private function normalizeCountryCode(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $clean = strtoupper(trim($value));
        return preg_match('/^[A-Z]{2}$/', $clean) ? $clean : null;
    }

    private function resolveBillingProfile(Request $request, ?string $customerName): array
    {
        $fallbackName = $customerName ?: 'Guest Customer';

        if ($this->isLocalCheckoutRequest($request)) {
            return [
                'name' => $customerName ?: 'Local Test Customer',
                'source' => 'local_static',
                'address' => [
                    'line1' => '123 Test Street',
                    'city' => 'New York',
                    'state' => 'NY',
                    'postal_code' => '10001',
                    'country' => 'US',
                ],
            ];
        }

        $countryContext = $this->resolveCountryFromRequest($request);
        $countryCode = $countryContext['country_code'] ?: 'US';

        return [
            'name' => $fallbackName,
            'source' => $countryContext['country_code'] ? 'ip_geo' : 'fallback_static',
            'address' => [
                'line1' => 'Address collected automatically',
                'city' => $countryContext['city'] ?: 'Customer City',
                'state' => $countryContext['region'] ?: 'Customer State',
                'postal_code' => $this->fallbackPostalCodeForCountry($countryCode),
                'country' => $countryCode,
            ],
        ];
    }

    private function fallbackPostalCodeForCountry(string $countryCode): string
    {
        return match (strtoupper(trim($countryCode))) {
            'IN' => '110001',
            'US' => '10001',
            'GB' => 'SW1A1AA',
            default => '00000',
        };
    }

    private function isLocalCheckoutRequest(Request $request): bool
    {
        $ip = (string) $request->ip();
        $host = strtolower((string) $request->getHost());

        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }

        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    private function pricingForReservation(Reservation $reservation, ?PromoCode $promoCode): array
    {
        return ServiceFeeCalculator::totalForPromo((float) $reservation->subtotal, $promoCode);
    }

    private function updateReservationPricing(
        Reservation $reservation,
        array $pricing,
        ?PromoCode $promoCode,
        array $extra = []
    ): void {
        $reservation->update(array_merge([
            'promo_code_id' => $promoCode?->id,
            'discount_amount' => $pricing['discount'],
            'portal_fee' => $pricing['portal_fee'],
            'service_fee' => $pricing['service_fee'],
            'total' => $pricing['total'],
        ], $extra));

        $reservation->refresh();
    }

    private function resolveCheckoutPromo(
        Reservation $reservation,
        ?string $rawPromoCode,
        bool $useExistingWhenBlank = false
    ): ?PromoCode {
        $normalizedCode = trim((string) $rawPromoCode);

        if ($normalizedCode === '') {
            if (
                $useExistingWhenBlank
                && $reservation->promoCode
                && !$reservation->promoCode->trashed()
                && $reservation->promoCode->isValid()
                && $reservation->promoCode->isApplicableToEvent($reservation->event)
            ) {
                return $reservation->promoCode;
            }

            return null;
        }

        $resolvedPromo = PromoCode::resolveForEvent($reservation->event, $normalizedCode);

        if ($resolvedPromo['message']) {
            throw ValidationException::withMessages([
                'promo_code' => $resolvedPromo['message'],
            ]);
        }

        $promoCode = $resolvedPromo['promo'];

        if (!$promoCode || !$promoCode->isValid()) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code is invalid, expired, or has reached its usage limit.',
            ]);
        }

        return $promoCode;
    }

    private function validateCheckoutContact(Request $request): array
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => strtolower(trim((string) $request->input('email'))),
            'phone' => trim((string) $request->input('phone')),
        ]);

        return $request->validate([
            'promo_code' => 'nullable|string|max:100',
            'name' => ['required', 'string', 'max:100', "regex:/^[A-Za-z .']+$/"],
            'email' => ['required', 'email:rfc', 'max:100'],
            'phone' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $phone = trim((string) $value);

                    if (!str_starts_with($phone, '07')) {
                        $fail('Phone number must start with 07');
                        return;
                    }

                    if (!preg_match('/^\d{11}$/', $phone)) {
                        $fail('Phone Number Must Be Exactly 11 digits');
                    }
                },
            ],
        ], [
            'name.required' => 'Full name is required.',
            'name.max' => 'Full name may not be greater than 100 characters.',
            'name.regex' => 'Full name may only contain letters, spaces, dots, and apostrophes.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address may not be greater than 100 characters.',
            'phone.required' => 'Phone number is required.',
        ]);
    }
}
