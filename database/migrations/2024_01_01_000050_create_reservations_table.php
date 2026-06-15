<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->uuid('token')->unique();
                $table->foreignId('event_id')->constrained()->onDelete('cascade');
                $table->string('session_id')->index();
                $table->string('customer_name')->nullable();
                $table->string('customer_email')->nullable();
                $table->string('customer_phone')->nullable();
                $table->foreignId('promo_code_id')->nullable()->constrained('promo_codes')->nullOnDelete();
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('total', 10, 2)->default(0);
                $table->dateTime('expires_at');
                $table->enum('status', ['pending', 'completed', 'expired', 'released'])->default('pending');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reservation_items')) {
            Schema::create('reservation_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('reservation_items');
        Schema::dropIfExists('reservations');
    }
};
