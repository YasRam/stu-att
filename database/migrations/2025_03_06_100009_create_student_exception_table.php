<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_exception', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('exception_type_id')->constrained('exception_types')->cascadeOnDelete();
            $table->primary(['student_id', 'exception_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_exception');
    }
};
