<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reservations') && ! Schema::hasColumn('reservations', 'stripe_payment_intent_id')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->string('stripe_payment_intent_id')->nullable()->after('total')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reservations') && Schema::hasColumn('reservations', 'stripe_payment_intent_id')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('stripe_payment_intent_id');
            });
        }
    }
};
