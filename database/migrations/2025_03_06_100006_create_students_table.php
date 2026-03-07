<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->timestamp('registered_at')->nullable();
            $table->string('full_name', 150);
            $table->char('national_id', 14)->unique()->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M', 'F'])->nullable();
            $table->char('birthplace_code', 2)->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->foreignId('stage_id')->index()->constrained('stages')->cascadeOnDelete();
            $table->enum('student_type', ['مستجد', 'مقيد'])->default('مستجد');
            $table->string('school_name')->nullable();
            $table->enum('school_schedule', ['صباحي', 'مسائي'])->nullable();
            $table->foreignId('enrollment_status_id')->index()->constrained('enrollment_statuses')->cascadeOnDelete()->default(1);
            $table->string('phone', 11)->nullable();
            $table->string('mobile', 11)->nullable();
            $table->string('relative_phone', 11)->nullable();
            $table->text('address')->nullable();
            $table->text('important_notes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('national_id');
            $table->index('gender');
            $table->index('birthplace_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
