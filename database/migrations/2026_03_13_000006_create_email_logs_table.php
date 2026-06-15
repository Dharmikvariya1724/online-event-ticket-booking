<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('email_logs')) {
            Schema::create('email_logs', function (Blueprint $table) {
                $table->id();
                $table->string('to');
                $table->string('subject')->nullable();
                $table->string('status', 20)->default('sent');
                $table->string('mailable')->nullable();
                $table->string('context_type')->nullable();
                $table->unsignedBigInteger('context_id')->nullable();
                $table->text('error')->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index(['context_type', 'context_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
