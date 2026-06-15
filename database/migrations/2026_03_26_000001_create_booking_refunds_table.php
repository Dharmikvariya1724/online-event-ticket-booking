<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking_refunds')) {
            return;
        }

        Schema::create('booking_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_refund_id')->nullable()->index();
            $table->decimal('original_total', 10, 2);
            $table->decimal('refunded_amount', 10, 2);
            $table->decimal('remaining_total', 10, 2);
            $table->string('currency', 3)->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('refunded_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_refunds');
    }
};
