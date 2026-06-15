<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('events') || Schema::hasColumn('events', 'ticket_validation_ends_at')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('ticket_validation_ends_at')->nullable()->after('ends_at');
        });

        DB::table('events')
            ->select(['id', 'starts_at'])
            ->orderBy('id')
            ->chunkById(100, function ($events) {
                foreach ($events as $event) {
                    if (!$event->starts_at) {
                        continue;
                    }

                    DB::table('events')
                        ->where('id', $event->id)
                        ->update([
                            'ticket_validation_ends_at' => Carbon::parse($event->starts_at)->addHours(2),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('events') || !Schema::hasColumn('events', 'ticket_validation_ends_at')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('ticket_validation_ends_at');
        });
    }
};
