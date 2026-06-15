<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Organiser;
use App\Models\TicketTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $organisers = Organiser::all();
        if ($organisers->isEmpty()) {
            $this->command->warn('No organisers found. Run OrganiserSeeder first.');
            return;
        }

        $events = [
            [
                'title'       => 'Summer Beats Festival 2025',
                'category'    => 'Music',
                'city'        => 'London',
                'venue_name'  => 'Victoria Park',
                'description' => 'The UK\'s premier summer music festival returns with three stages, 40+ artists, and an atmosphere like no other.',
                'starts_at'   => now()->addDays(30)->setTime(14, 0),
                'ends_at'     => now()->addDays(30)->setTime(23, 30),
                'status'      => 'published',
                'is_featured' => true,
                'tiers'       => [
                    ['name'=>'General Admission','price'=>45.00,'qty'=>500],
                    ['name'=>'VIP','price'=>120.00,'qty'=>100],
                    ['name'=>'Early Bird','price'=>35.00,'qty'=>50],
                ],
            ],
            [
                'title'       => 'London Tech Summit 2025',
                'category'    => 'Technology',
                'city'        => 'London',
                'venue_name'  => 'ExCeL London',
                'description' => 'Join 3,000 tech leaders, founders, and engineers for two days of talks, demos, and networking.',
                'starts_at'   => now()->addDays(45)->setTime(9, 0),
                'ends_at'     => now()->addDays(46)->setTime(18, 0),
                'status'      => 'published',
                'is_featured' => true,
                'tiers'       => [
                    ['name'=>'Standard Pass','price'=>299.00,'qty'=>200],
                    ['name'=>'Premium Pass','price'=>599.00,'qty'=>50],
                ],
            ],
            [
                'title'       => 'Stand-Up Comedy Night',
                'category'    => 'Comedy',
                'city'        => 'Manchester',
                'venue_name'  => 'The Comedy Store',
                'description' => 'A night of world-class stand-up from some of the UK\'s best comedians.',
                'starts_at'   => now()->addDays(14)->setTime(19, 30),
                'ends_at'     => now()->addDays(14)->setTime(22, 0),
                'status'      => 'published',
                'is_featured' => false,
                'tiers'       => [
                    ['name'=>'Standard','price'=>25.00,'qty'=>150],
                ],
            ],
            [
                'title'       => 'Art & Culture Exhibition',
                'category'    => 'Arts & Culture',
                'city'        => 'Birmingham',
                'venue_name'  => 'Birmingham Museum',
                'description' => 'A stunning exhibition featuring 200 works from emerging UK artists.',
                'starts_at'   => now()->addDays(7)->setTime(10, 0),
                'ends_at'     => now()->addDays(7)->setTime(18, 0),
                'status'      => 'published',
                'is_featured' => false,
                'tiers'       => [
                    ['name'=>'Adult Admission','price'=>15.00,'qty'=>300],
                    ['name'=>'Student','price'=>8.00,'qty'=>100],
                    ['name'=>'Under 16','price'=>0,'qty'=>100],
                ],
            ],
            [
                'title'       => 'Food & Drink Festival',
                'category'    => 'Food & Drink',
                'city'        => 'Bristol',
                'venue_name'  => 'Harbourside',
                'description' => 'Over 80 street food vendors, craft beers, and live music across Bristol\'s iconic harbourside.',
                'starts_at'   => now()->addDays(21)->setTime(11, 0),
                'ends_at'     => now()->addDays(21)->setTime(21, 0),
                'status'      => 'published',
                'is_featured' => true,
                'tiers'       => [
                    ['name'=>'Day Ticket','price'=>12.00,'qty'=>600],
                    ['name'=>'VIP Early Access','price'=>30.00,'qty'=>60],
                ],
            ],
        ];

        foreach ($events as $data) {
            $tiers = $data['tiers'];
            unset($data['tiers']);

            $data['organiser_id']  = $organisers->random()->id;
            $data['venue_address'] = '1 Event Way';
            $data['postcode']      = 'EC1A 1BB';
            $data['slug']          = Event::uniqueSlug($data['title']);

            $event = Event::firstOrCreate(['slug' => $data['slug']], $data);

            if ($event->wasRecentlyCreated) {
                $totalCapacity = 0;
                foreach ($tiers as $i => $tier) {
                    TicketTier::create([
                        'event_id'           => $event->id,
                        'name'               => $tier['name'],
                        'price'              => $tier['price'],
                        'total_quantity'     => $tier['qty'],
                        'available_quantity' => $tier['qty'],
                        'min_per_order'      => 1,
                        'max_per_order'      => 10,
                        'is_active'          => true,
                        'sort_order'         => $i,
                    ]);
                    $totalCapacity += $tier['qty'];
                }
                $event->update(['total_capacity' => $totalCapacity]);
            }
        }
    }
}