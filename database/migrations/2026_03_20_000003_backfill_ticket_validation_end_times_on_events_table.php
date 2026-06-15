<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('events') || !Schema::hasColumn('events', 'ticket_validation_ends_at')) {
            return;
        }

        DB::table('events')
            ->select(['id', 'starts_at', 'ends_at', 'ticket_validation_ends_at'])
            ->orderBy('id')
            ->chunkById(100, function ($events) {
                foreach ($events as $event) {
                    if (!$event->ends_at) {
                        continue;
                    }

                    $currentEnd = $event->ticket_validation_ends_at ? Carbon::parse($event->ticket_validation_ends_at) : null;
                    $legacyDefaultEnd = $event->starts_at ? Carbon::parse($event->starts_at)->addHours(2) : null;

                    if (!$currentEnd || ($legacyDefaultEnd && $currentEnd->equalTo($legacyDefaultEnd))) {
                        DB::table('events')
                            ->where('id', $event->id)
                            ->update([
                                'ticket_validation_ends_at' => Carbon::parse($event->ends_at),
                            ]);
                    }
                }
            });
    }

    public function down(): void
    {
    }
};
