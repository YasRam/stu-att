<?php

namespace Database\Factories;

use App\Models\Stage;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $year = $this->faker->numberBetween(2005, 2012);
        $month = $this->faker->numberBetween(1, 12);
        $day = $this->faker->numberBetween(1, 28);
        $century = $year >= 2000 ? 3 : 2;
        $yy = $year % 100;
        $genderDigit = $this->faker->randomElement([1, 3, 5, 7, 9, 2, 4, 6, 8]);
        $seq = $this->faker->unique()->numberBetween(100, 999);
        $gov = $this->faker->numberBetween(1, 27);
        $check = $this->faker->numberBetween(0, 9);
        $nationalId = sprintf('%d%02d%02d%02d%02d%03d%d%d', $century, $yy, $month, $day, $gov, $seq, $genderDigit, $check);
        if (strlen($nationalId) !== 14) {
            $nationalId = str_pad($nationalId, 14, '0');
        }

        $stage = Stage::query()->inRandomOrder()->first();
        if (!$stage) {
            $stage = Stage::factory()->create();
        }

        return [
            'full_name' => $this->faker->name(),
            'national_id' => $nationalId,
            'birth_date' => sprintf('%04d-%02d-%02d', $year, $month, $day),
            'gender' => in_array($genderDigit, [1, 3, 5, 7, 9]) ? 'M' : 'F',
            'stage_id' => $stage->id,
            'student_type' => $this->faker->randomElement(['مستجد', 'مقيد']),
            'school_schedule' => $this->faker->optional(0.6)->randomElement(['صباحي', 'مسائي']),
            'enrollment_status_id' => \App\Models\EnrollmentStatus::query()->inRandomOrder()->first()?->id ?? 1,
            'phone' => $this->faker->optional(0.7)->numerify('01########'),
            'mobile' => $this->faker->optional(0.6)->numerify('01########'),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
