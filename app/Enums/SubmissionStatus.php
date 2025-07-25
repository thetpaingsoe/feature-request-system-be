<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Reviewing = 'reviewing';
    case Feedback = 'feedback';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'pending',
            self::Approved => 'approved',
            self::Rejected => 'rejected',
            self::Reviewing => 'reviewing',
            self::Feedback => 'feedback'
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
