<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case ADMIN = 'admin';
    case AGENT = 'agent';
    case FRONTDESK = 'frontdesk';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::AGENT => 'Agent',
            self::FRONTDESK => 'Frontdesk',
        };
    }
}
