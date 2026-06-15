<?php

namespace App\Console\Commands;

use App\Repositories\TicketReservationRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * ExpireReservations
 *
 * Runs every minute via scheduler.
 * Delegates all locking/inventory logic to TicketReservationRepository.
 * Safe to run concurrently on multiple workers (DB-level locks prevent double-release).
 */
class ExpireReservations extends Command
{
    protected $signature   = 'tickets:expire-reservations';
    protected $description = 'Release expired ticket reservations and restore inventory.';

    public function handle(TicketReservationRepository $repo): int
    {
        $count = $repo->expireStale();
        $this->info("Released {$count} expired reservation(s).");
        return Command::SUCCESS;
    }
}