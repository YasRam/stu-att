<?php

namespace Database\Factories;

use App\Models\DailySession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailySessionFactory extends Factory
{
    protected $model = DailySession::class;

    public function definition(): array
    {
        $groups = Student::query()->distinct()->pluck('group_name')->filter()->toArray();
        $group = $groups ? $this->faker->randomElement($groups) : '3ب بنين تأسيس';

        return [
            'session_date' => $this->faker->dateTimeBetween('-2 weeks', 'now')->format('Y-m-d'),
            'subject_name' => $this->faker->randomElement(['رياضيات', 'عربي', 'علوم', 'لغة إنجليزية']),
            'stage_or_group' => $group,
            'status' => 'normal',
            'teacher_id' => User::factory(),
        ];
    }
}
