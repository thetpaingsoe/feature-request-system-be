<?php

namespace App\Enums;

enum FeatureRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Reviewed = 'reviewed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'pending',
            self::Approved => 'approved',
            self::Rejected => 'rejected',
            self::Reviewed => 'reviewed'
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
