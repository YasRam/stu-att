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
            self::Normal => __('Normal'),
            self::Exam => __('Exam'),
            self::Cancelled => __('Cancelled'),
            self::Other => __('Other'),
        };
    }
}
