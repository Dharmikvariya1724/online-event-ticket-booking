<?php

namespace App\Support;

class EventValidationRules
{
    public static function rules(bool $isUpdate = false): array
    {
        return [
            'title' => 'required|string|max:50',
            'short_description' => 'nullable|string|max:255',
            'description' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (mb_strlen(trim(strip_tags((string) $value))) > 5000) {
                        $fail('The full description may not be greater than 5000 characters.');
                    }
                },
            ],
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'banner_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'category' => 'required|string|max:100',
            'starts_at' => $isUpdate ? 'required|date' : 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'ticket_validation_starts_at' => 'required|date',
            'ticket_validation_ends_at' => 'required|date|after:ticket_validation_starts_at',
            'venue_name' => 'required|string|max:50',
            'venue_address' => 'required|string|max:300',
            'city' => 'required|string|max:50',
            'country' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'parking_info' => 'nullable|string|max:1000',
            'refund_policy' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (mb_strlen(trim(strip_tags((string) $value))) > 1000) {
                        $fail('The refund policy may not be greater than 1000 characters.');
                    }
                },
            ],
            'lineup_names' => 'nullable|array',
            'lineup_names.*' => 'nullable|string|max:50',
            'lineup_roles' => 'nullable|array',
            'lineup_roles.*' => 'nullable|string|max:50',
            'lineup_times' => 'nullable|array',
            'lineup_times.*' => ['nullable', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'status' => 'nullable|in:draft,published',
            'is_featured' => 'nullable|boolean',
        ];
    }

    public static function messages(): array
    {
        return [
            'lineup_names.*.max' => 'Performer Name maximum limit reached.',
            'lineup_roles.*.max' => 'Role / Band maximum limit reached.',
            'lineup_times.*.regex' => 'Time must be in valid HH:MM format (e.g. 20:00)',
        ];
    }
}
