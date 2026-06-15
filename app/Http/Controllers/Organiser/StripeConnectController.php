<?php

namespace App\Http\Controllers\Organiser;

use App\Http\Controllers\Controller;
use App\Services\StripeConnectService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\PermissionException;
use Throwable;

class StripeConnectController extends Controller
{
    public function __construct(private readonly StripeConnectService $stripeConnect)
    {}

    public function connect(Request $request)
    {
        $organiser = $request->attributes->get('organiser');

        try {
            $accountId = $this->stripeConnect->ensureExpressAccount($organiser);
            $url = $this->stripeConnect->createOnboardingLink(
                $accountId,
                route('organiser.stripe.refresh'),
                route('organiser.stripe.return')
            );

            return redirect()->away($url);
        } catch (Throwable $e) {
            $this->logStripeError('[Stripe Connect] Onboarding init failed', $e, $organiser?->id);

            return back()->withErrors([
                'stripe' => $this->stripeErrorMessage(
                    $e,
                    'Unable to start Stripe onboarding right now. Please try again.'
                ),
            ]);
        }
    }

    public function refresh(Request $request)
    {
        return $this->connect($request);
    }

    public function return(Request $request)
    {
        $organiser = $request->attributes->get('organiser');

        try {
            $isComplete = $this->stripeConnect->syncOnboardingStatus($organiser);
            $message = $isComplete
                ? 'Stripe account connected successfully.'
                : 'Stripe onboarding is not complete yet. Please finish onboarding to receive payouts.';

            return redirect()->route('organiser.profile.show')->with('success', $message);
        } catch (Throwable $e) {
            $this->logStripeError('[Stripe Connect] Onboarding return failed', $e, $organiser?->id);

            return redirect()->route('organiser.profile.show')
                ->withErrors([
                    'stripe' => $this->stripeErrorMessage(
                        $e,
                        'Unable to verify Stripe onboarding status. Please try again.'
                    ),
                ]);
        }
    }

    private function stripeErrorMessage(Throwable $e, string $fallback): string
    {
        if ($e->getMessage() === StripeConnectService::MISSING_SECRET_MESSAGE) {
            return 'Stripe payouts are not configured yet. Please contact the site admin.';
        }

        if ($e instanceof AuthenticationException) {
            return 'Stripe authentication failed. Please ask the site admin to verify the Stripe API keys.';
        }

        if ($e instanceof PermissionException || $this->looksLikeConnectSetupIssue($e->getMessage())) {
            return 'Stripe Connect is not enabled on this site yet. Please ask the site admin to enable Connect for the platform Stripe account.';
        }

        return $fallback;
    }

    private function looksLikeConnectSetupIssue(string $message): bool
    {
        return Str::contains($message, [
            'signed up for Connect',
            'create new accounts',
            'Connect platform',
        ], true);
    }

    private function logStripeError(string $message, Throwable $e, ?int $organiserId = null): void
    {
        $context = [
            'organiser_id' => $organiserId,
            'exception' => $e::class,
        ];

        if ($e instanceof ApiErrorException) {
            $context['stripe_http_status'] = $e->getHttpStatus();
            $context['stripe_request_id'] = $e->getRequestId();
            $context['stripe_code'] = $e->getStripeCode();
        }

        Log::error($message . ': ' . $e->getMessage(), array_filter(
            $context,
            static fn ($value) => $value !== null && $value !== ''
        ));
    }
}
