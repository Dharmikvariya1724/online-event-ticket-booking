<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PosterAIService
{
    public function scanPoster(string $imageUrl): ?array
    {
        $provider = Str::lower((string) config('services.poster_ai.provider', 'groq'));

        return match ($provider) {
            'groq' => $this->scanWithGroq($imageUrl),
            default => null,
        };
    }

    private function scanWithGroq(string $imageUrl): ?array
    {
        $apiKey = trim((string) config('services.poster_ai.groq.api_key', ''));
        $model = trim((string) config('services.poster_ai.groq.model', ''));
        $url = trim((string) config('services.poster_ai.groq.url', 'https://api.groq.com/openai/v1/chat/completions'));
        $timeout = (int) config('services.poster_ai.groq.timeout', 30);

        if ($apiKey === '' || $model === '' || trim($imageUrl) === '') {
            return null;
        }

        $response = $this->sendGroqRequest($imageUrl, $apiKey, $model, $url, $timeout, true);

        if (!$response->successful()) {
            $response = $this->sendGroqRequest($imageUrl, $apiKey, $model, $url, $timeout, false);
        }

        if (!$response->successful()) {
            Log::warning('[PosterAIService] Groq poster parsing request was not successful.', [
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 1000),
            ]);

            return null;
        }

        $content = $this->extractMessageContent($response->json());
        if ($content === null) {
            return null;
        }

        return $this->decodeJsonPayload($content);
    }

    private function sendGroqRequest(
        string $imageUrl,
        string $apiKey,
        string $model,
        string $url,
        int $timeout,
        bool $preferJsonResponseFormat
    ) {
        $payload = [
            'model' => $model,
            'temperature' => 0.2,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an AI that extracts structured event data from a poster image and always reply with valid JSON only.',
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $this->buildPromptText(),
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $imageUrl,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if ($preferJsonResponseFormat) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        return Http::withToken($apiKey)
            ->acceptJson()
            ->timeout($timeout)
            ->retry(1, 250, throw: false)
            ->post($url, $payload);
    }

    private function buildPromptText(): string
    {
        $today = now()->format('Y-m-d');
        $categories = implode(', ', Event::CATEGORIES);

        return <<<PROMPT
You are an AI that extracts structured event data from a poster image.

Analyze the poster carefully and return ONLY valid JSON.

Fields required:
- event_title
- short_description
- full_description
- start_datetime
- end_datetime
- venue_name
- city
- address
- postcode

Optional fields:
- category
- parking_transport_info
- refund_policy

Return format:
{
  "event_title": "",
  "short_description": "",
  "full_description": "",
  "start_datetime": "",
  "end_datetime": "",
  "venue_name": "",
  "city": "",
  "address": "",
  "postcode": "",
  "category": "",
  "parking_transport_info": "",
  "refund_policy": ""
}

Rules:
1. Extract exact text from the poster if available.
2. If any required field is missing, intelligently generate it based on the poster context.
3. If ONLY start_datetime exists, generate end_datetime exactly 2 hours later.
4. If ONLY end_datetime exists, generate start_datetime exactly 2 hours earlier.
5. If address exists, infer city and postcode.
6. Ensure all required fields are filled. Optional fields may be empty strings if the poster does not clearly provide them.
7. Keep descriptions meaningful and relevant.
8. event_title should be the most prominent event name or headline.
9. start_datetime and end_datetime must use 24-hour format YYYY-MM-DDTHH:MM.
10. If the poster has no year, choose the next upcoming reasonable future date.
11. If city cannot be inferred, use Surat.
12. If postcode cannot be inferred, use 395007.
13. category must be one of these exact values when confidently inferable: {$categories}. Otherwise return an empty string.
14. parking_transport_info should include parking, shuttle, metro, bus, train, or transport notes only if clearly present.
15. refund_policy should include refund, cancellation, transfer, or no-refund notes only if clearly present.
16. Never return null, markdown, comments, code fences, or extra keys outside this schema.
17. Keep event_title within 50 characters, short_description within 255 characters, venue_name within 50 characters, city within 50 characters, address within 300 characters, and postcode within 10 characters.

Today is {$today}.
PROMPT;
    }

    private function extractMessageContent(array $payload): ?string
    {
        $content = data_get($payload, 'choices.0.message.content');

        if (is_string($content)) {
            return $content;
        }

        if (!is_array($content)) {
            return null;
        }

        $texts = collect($content)
            ->map(function ($item) {
                if (is_string($item)) {
                    return $item;
                }

                if (is_array($item)) {
                    return $item['text'] ?? null;
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();

        return empty($texts) ? null : implode("\n", $texts);
    }

    private function decodeJsonPayload(string $content): ?array
    {
        $trimmed = trim($content);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/is', $trimmed, $matches) === 1) {
            $trimmed = $matches[1];
        } else {
            $firstBrace = strpos($trimmed, '{');
            $lastBrace = strrpos($trimmed, '}');

            if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
                $trimmed = substr($trimmed, $firstBrace, $lastBrace - $firstBrace + 1);
            }
        }

        $decoded = json_decode($trimmed, true);

        return is_array($decoded) ? $decoded : null;
    }
}
