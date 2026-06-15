<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'scanned_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->timestamp('scanned_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'scanned_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('scanned_at');
            });
        }
    }
};
