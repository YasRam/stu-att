<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('subject_name')->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('stage_id')->index()->constrained('stages')->cascadeOnDelete();
            $table->enum('gender_filter', ['M', 'F', 'all'])->default('all');
            $table->date('session_date')->index();
            $table->enum('session_type', [
                'اساسى',
                'تأسيس',
                'ازهرى',
                'امتحان',
                'غير دراسى',
                'ملغى',
            ])->default('اساسى');
            $table->string('academic_year', 9)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
