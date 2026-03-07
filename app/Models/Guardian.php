<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guardian extends Model
{
    protected $fillable = [
        'student_id',
        'relation',
        'is_primary_guardian',
        'full_name',
        'national_id',
        'birth_date',
        'gender',
        'birthplace_code',
        'age',
        'id_expiry',
        'job',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'id_expiry' => 'date',
            'is_primary_guardian' => 'boolean',
            'age' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
