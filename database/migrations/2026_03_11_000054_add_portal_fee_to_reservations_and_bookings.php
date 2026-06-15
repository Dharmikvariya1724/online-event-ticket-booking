<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reservations') && ! Schema::hasColumn('reservations', 'portal_fee')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->decimal('portal_fee', 10, 2)->default(0)->after('service_fee');
            });
        }

        if (Schema::hasTable('bookings') && ! Schema::hasColumn('bookings', 'portal_fee')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->decimal('portal_fee', 10, 2)->default(0)->after('service_fee');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reservations') && Schema::hasColumn('reservations', 'portal_fee')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('portal_fee');
            });
        }

        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'portal_fee')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('portal_fee');
            });
        }
    }
};
