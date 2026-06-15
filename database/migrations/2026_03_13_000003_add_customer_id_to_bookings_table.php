<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'customer_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreignId('customer_id')
                    ->nullable()
                    ->after('reservation_id')
                    ->constrained('customers')
                    ->nullOnDelete();
            });

            if (Schema::hasTable('customers')) {
                $customers = DB::table('customers')->select('id', 'email')->get()->keyBy('email');
                $bookings = DB::table('bookings')->select('id', 'customer_email')->whereNull('customer_id')->get();
                foreach ($bookings as $booking) {
                    $email = $booking->customer_email;
                    if (!$email || !$customers->has($email)) {
                        continue;
                    }
                    DB::table('bookings')->where('id', $booking->id)->update([
                        'customer_id' => $customers->get($email)->id,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'customer_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropConstrainedForeignId('customer_id');
            });
        }
    }
};
