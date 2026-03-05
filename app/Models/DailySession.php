<?php

namespace App\Models;

use App\Enums\SessionStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailySession extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = [
        'session_date',
        'subject_name',
        'stage_or_group',
        'status',
        'teacher_id',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'status' => SessionStatus::class,
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function getActivityLabel(): string
    {
        return $this->session_date?->format('Y-m-d') . ' — ' . $this->subject_name . ' (' . $this->stage_or_group . ')';
    }
}
