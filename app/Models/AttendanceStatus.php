<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceStatus extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'is_absent',
        'requires_reason',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'is_absent' => 'boolean',
            'requires_reason' => 'boolean',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
