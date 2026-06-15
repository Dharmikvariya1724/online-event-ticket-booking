<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('events', 'parking_info')) {
            Schema::table('events', function (Blueprint $table) {
                $table->text('parking_info')->nullable();
            });
        }

        if (! Schema::hasColumn('events', 'performer_lineup')) {
            Schema::table('events', function (Blueprint $table) {
                $table->json('performer_lineup')->nullable();
            });
        }

        if (! Schema::hasColumn('events', 'refund_policy')) {
            Schema::table('events', function (Blueprint $table) {
                $table->text('refund_policy')->nullable();
            });
        }

        if (! Schema::hasColumn('events', 'banner_image')) {
            Schema::table('events', function (Blueprint $table) {
                $table->string('banner_image')->nullable();
            });
        }

        if (! Schema::hasColumn('events', 'min_price_cache')) {
            Schema::table('events', function (Blueprint $table) {
                $table->decimal('min_price_cache', 10, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'parking_info')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('parking_info');
            });
        }

        if (Schema::hasColumn('events', 'performer_lineup')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('performer_lineup');
            });
        }

        if (Schema::hasColumn('events', 'refund_policy')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('refund_policy');
            });
        }

        if (Schema::hasColumn('events', 'banner_image')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('banner_image');
            });
        }

        if (Schema::hasColumn('events', 'min_price_cache')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('min_price_cache');
            });
        }
    }
};
