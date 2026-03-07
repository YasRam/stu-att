<?php

namespace Database\Seeders;

use App\Models\Stage;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([AttendanceStatusSeeder::class]);

        $admin = User::factory()->admin()->create([
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
        ]);

        $teacher = User::factory()->create([
            'name' => 'Demo Teacher',
            'email' => 'teacher@example.com',
        ]);

        // Stages must exist before students (students.stage_id FK).
        if (Stage::query()->doesntExist()) {
            Stage::insert([
                ['name_ar' => '1 ب', 'name_en' => 'P1', 'order_index' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name_ar' => '2 ب', 'name_en' => 'P2', 'order_index' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['name_ar' => '3 ب', 'name_en' => 'P3', 'order_index' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['name_ar' => '4 أ', 'name_en' => 'S1', 'order_index' => 4, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        Student::factory(25)->create();
    }
}
