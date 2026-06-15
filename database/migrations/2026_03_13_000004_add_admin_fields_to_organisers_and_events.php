<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('organisers')) {
            Schema::table('organisers', function (Blueprint $table) {
                if (!Schema::hasColumn('organisers', 'is_suspended')) {
                    $table->boolean('is_suspended')->default(false)->after('is_approved');
                }
                if (!Schema::hasColumn('organisers', 'suspended_at')) {
                    $table->timestamp('suspended_at')->nullable()->after('is_suspended');
                }
                if (!Schema::hasColumn('organisers', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('approved_at');
                }
                if (!Schema::hasColumn('organisers', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('rejected_at');
                }
                if (!Schema::hasColumn('organisers', 'approved_by_admin_id')) {
                    $table->foreignId('approved_by_admin_id')->nullable()
                        ->after('approved_at')
                        ->constrained('admins')
                        ->nullOnDelete();
                }
                if (!Schema::hasColumn('organisers', 'rejected_by_admin_id')) {
                    $table->foreignId('rejected_by_admin_id')->nullable()
                        ->after('rejection_reason')
                        ->constrained('admins')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                if (!Schema::hasColumn('events', 'approval_status')) {
                    $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                        ->default('pending')
                        ->after('status');
                }
                if (!Schema::hasColumn('events', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approval_status');
                }
                if (!Schema::hasColumn('events', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('approved_at');
                }
                if (!Schema::hasColumn('events', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('rejected_at');
                }
                if (!Schema::hasColumn('events', 'approved_by_admin_id')) {
                    $table->foreignId('approved_by_admin_id')->nullable()
                        ->after('rejection_reason')
                        ->constrained('admins')
                        ->nullOnDelete();
                }
                if (!Schema::hasColumn('events', 'rejected_by_admin_id')) {
                    $table->foreignId('rejected_by_admin_id')->nullable()
                        ->after('approved_by_admin_id')
                        ->constrained('admins')
                        ->nullOnDelete();
                }
            });

            // Approve existing events so public listings remain visible.
            if (Schema::hasColumn('events', 'approval_status')) {
                DB::table('events')
                    ->whereNull('approval_status')
                    ->orWhere('approval_status', 'pending')
                    ->update(['approval_status' => 'approved']);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('organisers')) {
            Schema::table('organisers', function (Blueprint $table) {
                if (Schema::hasColumn('organisers', 'rejected_by_admin_id')) {
                    $table->dropConstrainedForeignId('rejected_by_admin_id');
                }
                if (Schema::hasColumn('organisers', 'approved_by_admin_id')) {
                    $table->dropConstrainedForeignId('approved_by_admin_id');
                }
                if (Schema::hasColumn('organisers', 'rejection_reason')) {
                    $table->dropColumn('rejection_reason');
                }
                if (Schema::hasColumn('organisers', 'rejected_at')) {
                    $table->dropColumn('rejected_at');
                }
                if (Schema::hasColumn('organisers', 'suspended_at')) {
                    $table->dropColumn('suspended_at');
                }
                if (Schema::hasColumn('organisers', 'is_suspended')) {
                    $table->dropColumn('is_suspended');
                }
            });
        }

        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                if (Schema::hasColumn('events', 'rejected_by_admin_id')) {
                    $table->dropConstrainedForeignId('rejected_by_admin_id');
                }
                if (Schema::hasColumn('events', 'approved_by_admin_id')) {
                    $table->dropConstrainedForeignId('approved_by_admin_id');
                }
                if (Schema::hasColumn('events', 'rejection_reason')) {
                    $table->dropColumn('rejection_reason');
                }
                if (Schema::hasColumn('events', 'rejected_at')) {
                    $table->dropColumn('rejected_at');
                }
                if (Schema::hasColumn('events', 'approved_at')) {
                    $table->dropColumn('approved_at');
                }
                if (Schema::hasColumn('events', 'approval_status')) {
                    $table->dropColumn('approval_status');
                }
            });
        }
    }
};
