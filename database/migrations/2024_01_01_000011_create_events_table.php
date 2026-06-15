<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organiser_id')->constrained('organisers')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('banner')->nullable();
            $table->string('category')->default('General');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('venue_name');
            $table->string('venue_address');
            $table->string('city');
            $table->string('country')->default('UK');
            $table->string('postcode')->nullable();
            $table->text('parking_info')->nullable();
            $table->json('performer_lineup')->nullable();
            $table->text('refund_policy')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('total_capacity')->default(0);
            $table->timestamps();

            $table->index(['status', 'starts_at']);
            $table->index('organiser_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
