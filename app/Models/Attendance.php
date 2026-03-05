<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use LogsActivity;
    protected $fillable = [
        'daily_session_id',
        'student_id',
        'attendance_status_id',
        'reason',
        'taken_at',
        'taken_by',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'datetime',
        ];
    }

    public function dailySession(): BelongsTo
    {
        return $this->belongsTo(DailySession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attendanceStatus(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class);
    }

    public function takenByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function getActivityLabel(): string
    {
        $s = $this->student?->full_name ?? 'طالب#' . $this->student_id;
        $d = $this->dailySession?->session_date?->format('Y-m-d') ?? $this->daily_session_id;
        return "حضور: {$s} — {$d}";
    }
}
