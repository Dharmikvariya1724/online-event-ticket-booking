<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bookings')) {
            return;
        }

        if (!Schema::hasColumn('bookings', 'ticket_uuid')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->uuid('ticket_uuid')->nullable()->unique()->after('reference');
            });
        }

        if (!Schema::hasColumn('bookings', 'is_used')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->boolean('is_used')->default(false)->after('status');
            });
        }

        DB::table('bookings')
            ->select(['id', 'ticket_uuid'])
            ->orderBy('id')
            ->chunkById(200, function ($bookings) {
                foreach ($bookings as $booking) {
                    if (!empty($booking->ticket_uuid)) {
                        continue;
                    }

                    DB::table('bookings')
                        ->where('id', $booking->id)
                        ->update(['ticket_uuid' => (string) Str::uuid()]);
                }
            });

        DB::table('bookings')
            ->where(function ($query) {
                $query->whereNotNull('scanned_at')
                    ->orWhere('scanned_quantity', '>', 0);
            })
            ->update(['is_used' => true]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('bookings')) {
            return;
        }

        if (Schema::hasColumn('bookings', 'ticket_uuid')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('ticket_uuid');
            });
        }

        if (Schema::hasColumn('bookings', 'is_used')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('is_used');
            });
        }
    }
};
