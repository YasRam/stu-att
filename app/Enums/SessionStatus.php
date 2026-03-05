<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Normal = 'normal';
    case Exam = 'exam';
    case Cancelled = 'cancelled';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'عادي',
            self::Exam => 'امتحان',
            self::Cancelled => 'ملغى',
            self::Other => 'أخرى',
        };
    }
}
