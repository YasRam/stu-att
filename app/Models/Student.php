<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'registered_at',
        'full_name',
        'national_id',
        'birth_date',
        'gender',
        'birthplace_code',
        'age',
        'stage_id',
        'student_type',
        'school_name',
        'school_schedule',
        'enrollment_status_id',
        'phone',
        'mobile',
        'relative_phone',
        'address',
        'important_notes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'registered_at' => 'datetime',
            'age' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Student $student): void {
            if ($student->national_id && strlen($student->national_id) === 14) {
                $student->fillFromNationalId();
            }
        });
    }

    /**
     * Egyptian 14-digit national ID: century(1) + YY(2) + MM(2) + DD(2) + gov(2) + seq(3) + gender(1) + check(1)
     * Gender: 13th digit odd = male (M), even = female (F)
     * Birthplace: digits 8-9 (governorate code)
     */
    public function fillFromNationalId(): void
    {
        $id = $this->national_id;
        if (strlen($id) !== 14 || !ctype_digit($id)) {
            return;
        }
        $century = (int) substr($id, 0, 1);
        $yy = (int) substr($id, 1, 2);
        $mm = (int) substr($id, 3, 2);
        $dd = (int) substr($id, 5, 2);
        $year = $century === 2 ? 1900 + $yy : 2000 + $yy;
        if ($mm >= 1 && $mm <= 12 && $dd >= 1 && $dd <= 31) {
            $this->birth_date = sprintf('%04d-%02d-%02d', $year, $mm, $dd);
        }
        $genderDigit = (int) substr($id, 12, 1);
        $this->gender = ($genderDigit % 2 === 1) ? 'M' : 'F';
        $this->birthplace_code = substr($id, 7, 2);
        if ($this->birth_date) {
            $this->age = (int) $this->birth_date->diffInYears(now());
        }
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function enrollmentStatus(): BelongsTo
    {
        return $this->belongsTo(EnrollmentStatus::class);
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /** Stub: count unexcused absences in last 30 days (uses new attendances/sessions schema when available). */
    public function absentCountLast30Days(): int
    {
        return 0;
    }

    /** Stub: count unexcused absences in current year. */
    public function absentCountThisYear(): int
    {
        return 0;
    }

    public function hasHighAbsenceWarning(): bool
    {
        return false;
    }

    public function scopeWithHighAbsenceWarning(Builder $query): Builder
    {
        return $query;
    }
}
