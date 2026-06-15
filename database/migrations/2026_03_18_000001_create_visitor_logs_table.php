<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('country_code', 10)->nullable()->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('timezone', 100)->nullable();
            $table->string('method', 10)->index();
            $table->string('host', 255)->nullable()->index();
            $table->string('path', 2048)->nullable();
            $table->text('full_url')->nullable();
            $table->text('query')->nullable();
            $table->string('route_name', 255)->nullable()->index();
            $table->string('route_uri', 255)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->string('accept_language', 255)->nullable();
            $table->string('session_id', 100)->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('organiser_id')->nullable()->index();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->boolean('is_secure')->default(false);
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
