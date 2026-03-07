<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->unsignedTinyInteger('order_index')->default(0);
            $table->timestamps();
        });

        // Seed so students migration can default to "انتظار" (id 1)
        $now = now();
        \DB::table('enrollment_statuses')->insert([
            ['name_ar' => 'انتظار', 'name_en' => 'Pending', 'order_index' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'مقبول', 'name_en' => 'Accepted', 'order_index' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'مرفوض', 'name_en' => 'Rejected', 'order_index' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'بلاك ليست', 'name_en' => 'Blacklist', 'order_index' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'اعادة امتحان', 'name_en' => 'Retake', 'order_index' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'لم يمتحن', 'name_en' => 'Did not sit', 'order_index' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['name_ar' => 'انتظار التصحيح', 'name_en' => 'Awaiting correction', 'order_index' => 7, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_statuses');
    }
};
