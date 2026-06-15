<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('events')) {
            return;
        }

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organiser_id')->constrained('organisers')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('short_description', 300)->nullable();
            $table->string('venue_name');
            $table->string('venue_address');
            $table->string('city');
            $table->string('country')->default('GB');
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->string('image')->nullable();
            $table->string('category');
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('total_capacity')->default(0);
            $table->timestamps();
            $table->index(['status', 'starts_at']);
            $table->index('city');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
