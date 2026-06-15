<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && ! Schema::hasColumn('bookings', 'reminders_sent')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->json('reminders_sent')->nullable()->after('confirmation_sent_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'reminders_sent')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('reminders_sent');
            });
        }
    }
};
