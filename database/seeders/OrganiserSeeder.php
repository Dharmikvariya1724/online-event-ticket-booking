<?php

namespace Database\Seeders;

use App\Models\Organiser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganiserSeeder extends Seeder
{
    public function run(): void
    {
        $organisers = [
            [
                'name'         => 'Sarah Mitchell',
                'company_name' => 'Live Events UK',
                'email'        => 'sarah@liveevents.co.uk',
                'password'     => Hash::make('password123'),
                'phone'        => '+44 7700 900001',
                'website'      => 'https://liveevents.co.uk',
                'bio'          => 'UK\'s premier live events company, running festivals and concerts since 2008.',
                'is_approved'  => true,
                'approved_at'  => now(),
            ],
            [
                'name'         => 'James Harrington',
                'company_name' => 'Tech Conferences Ltd',
                'email'        => 'james@techconfs.io',
                'password'     => Hash::make('password123'),
                'phone'        => '+44 7700 900002',
                'website'      => 'https://techconfs.io',
                'bio'          => 'Delivering world-class technology conferences across the UK and Europe.',
                'is_approved'  => true,
                'approved_at'  => now(),
            ],
            [
                'name'         => 'Priya Patel',
                'company_name' => 'Art Space Events',
                'email'        => 'priya@artspace.uk',
                'password'     => Hash::make('password123'),
                'phone'        => '+44 7700 900003',
                'website'      => 'https://artspace.uk',
                'bio'          => 'Curating immersive art and cultural experiences in unconventional spaces.',
                'is_approved'  => true,
                'approved_at'  => now(),
            ],
        ];

        foreach ($organisers as $data) {
            Organiser::firstOrCreate(['email' => $data['email']], $data);
        }
    }
}