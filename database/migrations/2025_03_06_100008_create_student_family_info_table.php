<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_family_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->unsignedTinyInteger('children_under_5')->default(0);
            $table->unsignedTinyInteger('children_5_to_10')->default(0);
            $table->unsignedTinyInteger('boys_over_10')->default(0);
            $table->unsignedTinyInteger('girls_over_10')->default(0);
            $table->unsignedTinyInteger('siblings_in_activity')->default(0);
            $table->unsignedTinyInteger('family_members_count')->default(0);
            $table->text('family_circumstances')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_family_info');
    }
};
