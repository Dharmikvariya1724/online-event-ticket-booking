<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('organisers')) {
            Schema::table('organisers', function (Blueprint $table) {
                if (!Schema::hasColumn('organisers', 'stripe_account_id')) {
                    $table->string('stripe_account_id')->nullable()->after('last_active_at');
                }
                if (!Schema::hasColumn('organisers', 'stripe_onboarding_complete')) {
                    $table->boolean('stripe_onboarding_complete')->default(false)->after('stripe_account_id');
                }
            });
        }

        if (!Schema::hasTable('payouts')) {
            Schema::create('payouts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('stripe_payout_id')->index();
                $table->decimal('amount', 10, 2)->default(0);
                $table->string('currency', 10)->default('INR');
                $table->string('status', 30)->default('pending');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('organisers')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('organisers')) {
            Schema::table('organisers', function (Blueprint $table) {
                if (Schema::hasColumn('organisers', 'stripe_onboarding_complete')) {
                    $table->dropColumn('stripe_onboarding_complete');
                }
                if (Schema::hasColumn('organisers', 'stripe_account_id')) {
                    $table->dropColumn('stripe_account_id');
                }
            });
        }

        if (Schema::hasTable('payouts')) {
            Schema::drop('payouts');
        }
    }
};
