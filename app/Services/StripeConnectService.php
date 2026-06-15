<?php

namespace App\Services;

use App\Models\Organiser;
use Stripe\StripeClient;
use RuntimeException;

class StripeConnectService
{
    public const MISSING_SECRET_MESSAGE = 'Stripe secret key is not configured.';

    private ?StripeClient $client = null;

    public function ensureExpressAccount(Organiser $organiser): string
    {
        if (!empty($organiser->stripe_account_id)) {
            return $organiser->stripe_account_id;
        }

        $account = $this->client()->accounts->create([
            'type'  => 'express',
            'email' => $organiser->email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers'     => ['requested' => true],
            ],
        ]);

        $organiser->update([
            'stripe_account_id'         => $account->id,
            'stripe_onboarding_complete'=> false,
        ]);

        return $account->id;
    }

    public function createOnboardingLink(string $accountId, string $refreshUrl, string $returnUrl): string
    {
        $link = $this->client()->accountLinks->create([
            'account'     => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url'  => $returnUrl,
            'type'        => 'account_onboarding',
        ]);

        return $link->url;
    }

    public function syncOnboardingStatus(Organiser $organiser): bool
    {
        if (empty($organiser->stripe_account_id)) {
            $organiser->update(['stripe_onboarding_complete' => false]);
            return false;
        }

        $account = $this->client()->accounts->retrieve($organiser->stripe_account_id, []);
        $complete = (bool) ($account->details_submitted ?? false);
        $chargesEnabled = (bool) ($account->charges_enabled ?? false);
        $payoutsEnabled = (bool) ($account->payouts_enabled ?? false);

        $isComplete = $complete && $chargesEnabled && $payoutsEnabled;
        $organiser->update(['stripe_onboarding_complete' => $isComplete]);

        return $isComplete;
    }

    private function client(): StripeClient
    {
        if ($this->client instanceof StripeClient) {
            return $this->client;
        }

        $secret = config('services.stripe.secret');
        $secret = is_string($secret) ? trim($secret) : '';

        if ($secret === '') {
            throw new RuntimeException(self::MISSING_SECRET_MESSAGE);
        }

        $this->client = new StripeClient($secret);

        return $this->client;
    }
}
