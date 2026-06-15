<?php

namespace Database\Seeders;

use App\Models\Organiser;
use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $org1 = Organiser::where('email', 'sarah@liveevents.co.uk')->first();
        $org2 = Organiser::where('email', 'james@techconfs.io')->first();

        if (! $org1 || ! $org2) {
            $this->command?->warn('PromoCodeSeeder skipped: required organisers not found.');
            return;
        }

        $codes = [
            [
                'organiser_id' => $org1->id,
                'event_id'     => null,
                'code'         => 'SUMMER20',
                'type'         => 'percentage',
                'value'        => 20,
                'max_uses'     => 100,
                'expires_at'   => now()->addMonths(3),
                'is_active'    => true,
            ],
            [
                'organiser_id' => $org1->id,
                'event_id'     => null,
                'code'         => 'WELCOME10',
                'type'         => 'fixed',
                'value'        => 10,
                'max_uses'     => 500,
                'expires_at'   => now()->addYear(),
                'is_active'    => true,
            ],
            [
                'organiser_id' => $org2->id,
                'event_id'     => null,
                'code'         => 'DEVDISCOUNT',
                'type'         => 'percentage',
                'value'        => 15,
                'max_uses'     => 50,
                'expires_at'   => now()->addMonths(2),
                'is_active'    => true,
            ],
        ];

        foreach ($codes as $code) {
            PromoCode::updateOrCreate(
                ['code' => $code['code']],
                $code
            );
        }
    }
}
