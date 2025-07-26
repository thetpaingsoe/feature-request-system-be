<?php

namespace App\Enums;

enum SubmissionLogTypes: string
{
    case Create = 'create';
    case Update = 'update';
    case StatusChange = 'status_change';
    case SuggestionMessage = 'sugg_message';
    case SuggestionAccepted = 'sugg_accept';
    case SuggestionRejected = 'sugg_reject';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Create => 'pending',
            self::Update => 'update',
            self::StatusChange => 'status_change',
            self::SuggestionMessage => 'sugg_message',
            self::SuggestionAccepted => 'sugg_accept',
            self::SuggestionRejected => 'sugg_reject',
            self::Approved => 'approved',
            self::Rejected => 'rejected'
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
