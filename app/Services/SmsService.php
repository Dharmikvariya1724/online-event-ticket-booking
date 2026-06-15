<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $provider;

    public function __construct()
    {
        $this->provider = config('notifications.sms_provider', 'twilio');
    }

    /**
     * Send an SMS message.
     * Returns true on success, false on failure.
     */
    public function send(string $to, string $message): bool
    {
        $to = $this->normaliseNumber($to);

        if (!$to) {
            Log::warning('[SmsService] Invalid phone number – skipping SMS.');
            return false;
        }

        try {
            return match ($this->provider) {
                'twilio' => $this->sendViaTwilio($to, $message),
                'vonage' => $this->sendViaVonage($to, $message),
                'null'   => $this->sendViaNull($to, $message),
                default  => throw new \RuntimeException('Unknown SMS provider: ' . $this->provider),
            };
        } catch (\Exception $e) {
            Log::error('[SmsService] Delivery failed', [
                'provider' => $this->provider,
                'to'       => $to,
                'error'    => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ── Providers ─────────────────────────────────────────────────

    private function sendViaTwilio(string $to, string $message): bool
    {
        $sid   = config('notifications.twilio.sid');
        $token = config('notifications.twilio.token');
        $from  = config('notifications.twilio.from');

        if (!$sid || !$token || !$from) {
            Log::warning('[SmsService] Twilio credentials not configured.');
            return false;
        }

        // Twilio REST API (no SDK dependency required)
        $url  = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        $data = ['From' => $from, 'To' => $to, 'Body' => $message];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => "{$sid}:{$token}",
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            throw new \RuntimeException('cURL error: ' . $curlErr);
        }

        if ($httpCode >= 400) {
            $body = json_decode($response, true);
            throw new \RuntimeException('Twilio error ' . $httpCode . ': ' . ($body['message'] ?? 'Unknown'));
        }

        Log::info('[SmsService] Twilio SMS sent', ['to' => $to, 'http_code' => $httpCode]);
        return true;
    }

    private function sendViaVonage(string $to, string $message): bool
    {
        $key    = config('notifications.vonage.key');
        $secret = config('notifications.vonage.secret');
        $from   = config('notifications.vonage.from', 'Ticketly');

        if (!$key || !$secret) {
            Log::warning('[SmsService] Vonage credentials not configured.');
            return false;
        }

        $url  = 'https://rest.nexmo.com/sms/json';
        $data = [
            'api_key'    => $key,
            'api_secret' => $secret,
            'to'         => ltrim($to, '+'),
            'from'       => $from,
            'text'       => $message,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            throw new \RuntimeException('cURL error: ' . $curlErr);
        }

        $body   = json_decode($response, true);
        $status = $body['messages'][0]['status'] ?? '1';

        if ($status !== '0') {
            $errText = $body['messages'][0]['error-text'] ?? 'Unknown error';
            throw new \RuntimeException('Vonage error: ' . $errText);
        }

        Log::info('[SmsService] Vonage SMS sent', ['to' => $to]);
        return true;
    }

    private function sendViaNull(string $to, string $message): bool
    {
        // Useful for testing / local dev
        Log::info('[SmsService] NULL provider – SMS NOT sent (dev mode)', [
            'to'      => $to,
            'message' => $message,
        ]);
        return true;
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function normaliseNumber(string $number): ?string
    {
        if (empty($number)) return null;
        // Strip non-numeric except leading +
        $clean = preg_replace('/[^\d+]/', '', $number);
        // Prepend UK country code if starts with 07
        if (str_starts_with($clean, '07')) {
            $clean = '+44' . substr($clean, 1);
        }
        // Basic sanity: must be 7–15 digits
        $digits = preg_replace('/[^\d]/', '', $clean);
        if (strlen($digits) < 7 || strlen($digits) > 15) return null;

        return $clean;
    }
}