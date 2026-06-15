<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->boolean('is_suspended')->default(false);
                $table->timestamp('suspended_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('bookings') && Schema::hasTable('customers')) {
            $existing = DB::table('customers')->pluck('email')->all();
            $rows = DB::table('bookings')
                ->select('customer_email as email', 'customer_name as name', 'customer_phone as phone')
                ->whereNotNull('customer_email')
                ->distinct()
                ->get();

            foreach ($rows as $row) {
                if (in_array($row->email, $existing, true)) {
                    continue;
                }
                DB::table('customers')->insert([
                    'email' => $row->email,
                    'name' => $row->name,
                    'phone' => $row->phone,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $existing[] = $row->email;
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
