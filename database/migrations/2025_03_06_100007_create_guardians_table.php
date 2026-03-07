<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->index()->constrained('students')->cascadeOnDelete();
            $table->enum('relation', ['أب', 'أم', 'جد', 'عم', 'خال', 'غيره']);
            $table->boolean('is_primary_guardian')->default(false);
            $table->string('full_name', 150);
            $table->char('national_id', 14)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M', 'F'])->nullable();
            $table->char('birthplace_code', 2)->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->date('id_expiry')->nullable();
            $table->string('job')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
