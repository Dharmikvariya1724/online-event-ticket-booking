<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'scanned_quantity')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->unsignedInteger('scanned_quantity')->default(0)->after('scanned_at');
            });

            DB::table('bookings')
                ->whereNotNull('scanned_at')
                ->where('scanned_quantity', 0)
                ->update(['scanned_quantity' => 1]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'scanned_quantity')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('scanned_quantity');
            });
        }
    }
};
