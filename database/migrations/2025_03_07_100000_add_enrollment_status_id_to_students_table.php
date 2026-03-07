<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('students')) {
            return;
        }

        if (Schema::hasColumn('students', 'enrollment_status_id')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('enrollment_status_id')->nullable()->default(1)->after('school_schedule');
        });

        if (Schema::hasColumn('students', 'enrollment_status')) {
            $statuses = DB::table('enrollment_statuses')->pluck('id', 'name_ar');
            $defaultId = $statuses['انتظار'] ?? 1;
            foreach (DB::table('students')->select('id', 'enrollment_status')->get() as $row) {
                $statusId = $statuses[$row->enrollment_status ?? ''] ?? $defaultId;
                DB::table('students')->where('id', $row->id)->update(['enrollment_status_id' => $statusId]);
            }
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('enrollment_status');
            });
        }

        Schema::table('students', function (Blueprint $table) {
            $table->foreign('enrollment_status_id')->references('id')->on('enrollment_statuses')->cascadeOnDelete();
            $table->index('enrollment_status_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('students') || !Schema::hasColumn('students', 'enrollment_status_id')) {
            return;
        }
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['enrollment_status_id']);
            $table->dropColumn('enrollment_status_id');
        });
        if (!Schema::hasColumn('students', 'enrollment_status')) {
            Schema::table('students', function (Blueprint $table) {
                $table->enum('enrollment_status', [
                    'مقبول', 'انتظار', 'مرفوض', 'بلاك ليست',
                    'اعادة امتحان', 'لم يمتحن', 'انتظار التصحيح',
                ])->default('انتظار')->after('school_schedule');
            });
        }
    }
};
