<?php

namespace Database\Seeders;

use App\Models\DailySession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([AttendanceStatusSeeder::class]);

        $admin = User::factory()->admin()->create([
            'name' => 'مدير النظام',
            'email' => 'admin@example.com',
        ]);

        $teacher = User::factory()->create([
            'name' => 'معلم تجريبي',
            'email' => 'teacher@example.com',
        ]);

        Student::factory(25)->create();

        foreach (['3ب بنين تأسيس', '3ب بنات تأسيس'] as $group) {
            if (Student::where('group_name', $group)->exists()) {
                DailySession::factory()->count(2)->create([
                    'teacher_id' => $teacher->id,
                    'stage_or_group' => $group,
                ]);
            }
        }
    }
}
