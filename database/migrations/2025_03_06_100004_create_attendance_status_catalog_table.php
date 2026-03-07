<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_status_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->enum('category', ['presence', 'excuse', 'event', 'action']);
            $table->boolean('counts_as_absent')->default(false);
            $table->boolean('blocks_attendance')->default(false);
            $table->boolean('requires_reason')->default(false);
            $table->string('color_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_status_catalog');
    }
};
