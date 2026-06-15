<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique();
                $table->foreignId('event_id')->constrained()->onDelete('cascade');
                $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
                $table->string('customer_name');
                $table->string('customer_email');
                $table->string('customer_phone')->nullable();
                $table->foreignId('promo_code_id')->nullable()->constrained('promo_codes')->nullOnDelete();
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('subtotal', 10, 2);
                $table->decimal('total', 10, 2);
                $table->string('currency', 3)->default('GBP');
                $table->string('stripe_session_id')->nullable()->index();
                $table->string('stripe_payment_intent_id')->nullable()->index();
                $table->string('stripe_charge_id')->nullable();
                $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded', 'partially_refunded'])->default('pending');
                $table->decimal('refund_amount', 10, 2)->nullable();
                $table->timestamp('refunded_at')->nullable();
                $table->text('refund_reason')->nullable();
                $table->timestamp('confirmation_sent_at')->nullable();
                $table->timestamps();
                $table->index(['customer_email', 'status']);
            });
        }

        if (! Schema::hasTable('booking_items')) {
            Schema::create('booking_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained()->onDelete('cascade');
                $table->foreignId('ticket_tier_id')->constrained()->onDelete('cascade');
                $table->unsignedTinyInteger('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('subtotal', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('bookings');
    }
};
