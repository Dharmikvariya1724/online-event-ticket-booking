<?php

namespace Tests\Feature;

use App\Models\Organiser;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrganiserPosterAutofillTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        config([
            'services.poster_ai.provider' => 'groq',
            'services.poster_ai.groq.api_key' => 'test-groq-key',
            'services.poster_ai.groq.model' => 'meta-llama/llama-4-scout-17b-16e-instruct',
            'services.poster_ai.groq.url' => 'https://api.groq.com/openai/v1/chat/completions',
            'services.poster_ai.groq.timeout' => 10,
        ]);
    }

    public function test_organiser_can_parse_poster_and_receive_normalized_autofill_json(): void
    {
        $organiser = $this->makeOrganiser();
        $startAt = now()->addDays(30)->setTime(19, 30);

        Http::fake([
            'https://api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'event_title' => 'Summer Beats Rooftop Party',
                                'short_description' => 'Sunset DJ sessions with cocktails and skyline views.',
                                'full_description' => 'Dance into the evening with a vibrant rooftop crowd, curated DJ sets, and a polished social atmosphere built around the poster theme.',
                                'start_datetime' => $startAt->format('Y-m-d\TH:i'),
                                'end_datetime' => '',
                                'venue_name' => 'Skyline Terrace',
                                'city' => '',
                                'address' => 'Skyline Terrace, 99 River Lane, London EC1A 1BB',
                                'postcode' => '',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->post(route('organiser.events.poster.parse'), [
                'poster' => UploadedFile::fake()->image('summer-beats-rooftop-party.png', 1200, 1600),
            ]);

        $response->assertOk();

        $payload = $response->json();

        $this->assertAutofillPayloadShape($payload);
        $this->assertSame('Summer Beats Rooftop Party', $payload['event_title']);
        $this->assertSame($startAt->format('Y-m-d\TH:i'), $payload['start_datetime']);
        $this->assertSame($startAt->copy()->addHours(2)->format('Y-m-d\TH:i'), $payload['end_datetime']);
        $this->assertSame('Skyline Terrace', $payload['venue_name']);
        $this->assertSame('London', $payload['city']);
        $this->assertSame('Skyline Terrace, 99 River Lane, London EC1A 1BB', $payload['address']);
        $this->assertSame('EC1A 1BB', $payload['postcode']);
        $this->assertAutofillPayloadHasUsableValues($payload);
    }

    public function test_parser_generates_missing_values_when_ai_returns_partial_poster_data(): void
    {
        $organiser = $this->makeOrganiser([
            'email' => 'poster.partial.organiser@example.com',
        ]);
        $startAt = now()->addDays(21)->setTime(18, 0);

        Http::fake([
            'https://api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'event_title' => 'Divine Kirtan Evening',
                                'short_description' => '',
                                'full_description' => '',
                                'start_datetime' => $startAt->format('Y-m-d\TH:i'),
                                'end_datetime' => '',
                                'venue_name' => '',
                                'city' => '',
                                'address' => '',
                                'postcode' => '',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->post(route('organiser.events.poster.parse'), [
                'poster' => UploadedFile::fake()->image('divine-kirtan-evening.png', 1200, 1600),
            ]);

        $response->assertOk();

        $payload = $response->json();
        $this->assertAutofillPayloadShape($payload);
        $this->assertAutofillPayloadHasUsableValues($payload);
        $this->assertSame('Divine Kirtan Evening', $payload['event_title']);
        $this->assertSame($startAt->format('Y-m-d\TH:i'), $payload['start_datetime']);
        $this->assertSame($startAt->copy()->addHours(2)->format('Y-m-d\TH:i'), $payload['end_datetime']);
        $this->assertSame('Surat', $payload['city']);
        $this->assertSame('395007', $payload['postcode']);
    }

    public function test_parser_generates_start_datetime_when_only_end_datetime_is_available(): void
    {
        $organiser = $this->makeOrganiser([
            'email' => 'poster.end-only.organiser@example.com',
        ]);
        $endAt = now()->addDays(12)->setTime(22, 0);

        Http::fake([
            'https://api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'event_title' => 'Tech Future Expo',
                                'short_description' => '',
                                'full_description' => '',
                                'start_datetime' => '',
                                'end_datetime' => $endAt->format('Y-m-d\TH:i'),
                                'venue_name' => '',
                                'city' => '',
                                'address' => '',
                                'postcode' => '',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->post(route('organiser.events.poster.parse'), [
                'poster' => UploadedFile::fake()->image('tech-future-expo.png', 1200, 1600),
            ]);

        $response->assertOk();

        $payload = $response->json();
        $this->assertAutofillPayloadShape($payload);
        $this->assertAutofillPayloadHasUsableValues($payload);
        $this->assertSame($endAt->copy()->subHours(2)->format('Y-m-d\TH:i'), $payload['start_datetime']);
        $this->assertSame($endAt->format('Y-m-d\TH:i'), $payload['end_datetime']);
    }

    public function test_parser_falls_back_to_generated_values_when_ai_response_is_invalid(): void
    {
        $organiser = $this->makeOrganiser();

        Http::fake([
            'https://api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'not valid json',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this
            ->withSession($this->organiserSession($organiser))
            ->post(route('organiser.events.poster.parse'), [
                'poster' => UploadedFile::fake()->image('kirtan-poster.png', 1200, 1600),
            ]);

        $response->assertOk();

        $payload = $response->json();
        $startAt = Carbon::createFromFormat('Y-m-d\TH:i', $payload['start_datetime']);
        $endAt = Carbon::createFromFormat('Y-m-d\TH:i', $payload['end_datetime']);

        $this->assertAutofillPayloadShape($payload);
        $this->assertAutofillPayloadHasUsableValues($payload);
        $this->assertSame('Surat', $payload['city']);
        $this->assertSame('Satsang Hall', $payload['venue_name']);
        $this->assertSame('395007', $payload['postcode']);
        $this->assertTrue($startAt->greaterThan(now()));
        $this->assertTrue($endAt->greaterThan($startAt));
        $this->assertSame($startAt->copy()->addHours(2)->format('Y-m-d\TH:i'), $payload['end_datetime']);
    }

    public function test_poster_parse_endpoint_requires_organiser_authentication_for_json_requests(): void
    {
        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('organiser.events.poster.parse'), [
                'poster' => UploadedFile::fake()->image('poster.png', 1200, 1600),
            ]);

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Unauthenticated.',
        ]);
    }

    private function makeOrganiser(array $overrides = []): Organiser
    {
        return Organiser::create(array_merge([
            'name' => 'Poster Organiser',
            'company_name' => 'Poster Co',
            'email' => 'poster.organiser@example.com',
            'password' => Hash::make('password123'),
            'phone' => '07123456789',
            'is_approved' => true,
        ], $overrides));
    }

    private function organiserSession(Organiser $organiser): array
    {
        return [
            'organiser_id' => $organiser->id,
            'organiser_last_active' => now()->timestamp,
        ];
    }

    private function assertAutofillPayloadShape(array $payload): void
    {
        $this->assertSame([
            'event_title',
            'short_description',
            'full_description',
            'start_datetime',
            'end_datetime',
            'venue_name',
            'city',
            'address',
            'postcode',
        ], array_keys($payload));
    }

    private function assertAutofillPayloadHasUsableValues(array $payload): void
    {
        foreach ($payload as $key => $value) {
            $this->assertIsString($value, sprintf('Expected "%s" to be a string.', $key));
            $this->assertNotSame('', trim($value), sprintf('Expected "%s" to be filled.', $key));
        }
    }
}
