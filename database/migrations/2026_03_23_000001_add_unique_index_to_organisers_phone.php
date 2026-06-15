<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicatePhones = DB::table('organisers')
            ->select('phone')
            ->whereNotNull('phone')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('phone');

        if ($duplicatePhones->isNotEmpty()) {
            throw new \RuntimeException('Duplicate organiser phone numbers must be cleaned before adding the unique index.');
        }

        Schema::table('organisers', function (Blueprint $table) {
            $table->unique('phone', 'organisers_phone_unique');
        });
    }

    public function down(): void
    {
        Schema::table('organisers', function (Blueprint $table) {
            $table->dropUnique('organisers_phone_unique');
        });
    }
};
