<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use Illuminate\Database\Seeder;

class AttendanceStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name_ar' => 'حاضر', 'name_en' => 'Present', 'is_absent' => false, 'requires_reason' => false, 'color' => 'success'],
            ['name_ar' => 'غائب', 'name_en' => 'Absent', 'is_absent' => true, 'requires_reason' => false, 'color' => 'danger'],
            ['name_ar' => 'معتذر', 'name_en' => 'Excused', 'is_absent' => true, 'requires_reason' => true, 'color' => 'warning'],
            ['name_ar' => 'إجازة', 'name_en' => 'On Leave', 'is_absent' => false, 'requires_reason' => true, 'color' => 'info'],
            ['name_ar' => 'استدعاء ولي أمر', 'name_en' => 'Parent Summoned', 'is_absent' => false, 'requires_reason' => true, 'color' => 'gray'],
        ];

        foreach ($statuses as $status) {
            AttendanceStatus::firstOrCreate(
                ['name_en' => $status['name_en']],
                $status
            );
        }
    }
}
