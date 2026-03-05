<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'full_name',
        'national_id',
        'birth_date',
        'gender',
        'stage',
        'group_name',
        'is_taasis',
        'is_azhary',
        'phone',
        'guardian_name',
        'guardian_phone',
        'guardian_national_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_taasis' => 'boolean',
            'is_azhary' => 'boolean',
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
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /** Absences without excuse in last 30 days (status: is_absent=true, requires_reason=false). */
    public function absentCountLast30Days(): int
    {
        return $this->attendances()
            ->whereHas('attendanceStatus', fn ($q) => $q->where('is_absent', true)->where('requires_reason', false))
            ->whereHas('dailySession', fn ($q) => $q->where('session_date', '>=', now()->subDays(30)))
            ->count();
    }

    /** Absences without excuse in current year. */
    public function absentCountThisYear(): int
    {
        $yearStart = now()->startOfYear()->format('Y-m-d');
        return $this->attendances()
            ->whereHas('attendanceStatus', fn ($q) => $q->where('is_absent', true)->where('requires_reason', false))
            ->whereHas('dailySession', fn ($q) => $q->where('session_date', '>=', $yearStart))
            ->count();
    }

    public function hasHighAbsenceWarning(): bool
    {
        return $this->absentCountLast30Days() > 5 || $this->absentCountThisYear() > 15;
    }

    public function scopeWithHighAbsenceWarning(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereIn('id', function ($sub) {
                $sub->select('student_id')
                    ->from('attendances')
                    ->join('attendance_statuses', 'attendances.attendance_status_id', '=', 'attendance_statuses.id')
                    ->join('daily_sessions', 'attendances.daily_session_id', '=', 'daily_sessions.id')
                    ->where('attendance_statuses.is_absent', true)
                    ->where('attendance_statuses.requires_reason', false)
                    ->where('daily_sessions.session_date', '>=', now()->subDays(30))
                    ->groupBy('student_id')
                    ->havingRaw('count(attendances.id) > 5');
            })->orWhereIn('id', function ($sub) {
                $sub->select('student_id')
                    ->from('attendances')
                    ->join('attendance_statuses', 'attendances.attendance_status_id', '=', 'attendance_statuses.id')
                    ->join('daily_sessions', 'attendances.daily_session_id', '=', 'daily_sessions.id')
                    ->where('attendance_statuses.is_absent', true)
                    ->where('attendance_statuses.requires_reason', false)
                    ->where('daily_sessions.session_date', '>=', now()->startOfYear())
                    ->groupBy('student_id')
                    ->havingRaw('count(attendances.id) > 15');
            });
        });
    }
}
