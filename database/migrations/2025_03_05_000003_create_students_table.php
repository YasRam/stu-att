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
            $table->string('full_name');
            $table->char('national_id', 14)->unique();
            $table->date('birth_date')->nullable();
            $table->char('gender', 1)->nullable(); // M / F
            $table->string('stage')->nullable(); // e.g. 3ب
            $table->string('group_name')->nullable(); // e.g. 3ب بنين تأسيس
            $table->boolean('is_taasis')->default(false);
            $table->boolean('is_azhary')->default(false);
            $table->string('phone')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->char('guardian_national_id', 14)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
