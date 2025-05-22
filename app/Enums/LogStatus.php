<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LogStatus: string implements HasIcon, HasLabel
{
    case SERVED = 'served';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case SKIPPED = 'skipped';
    case REQUEUED = 'requeued';

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SERVED => 'heroicon-o-arrow-path',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::CANCELLED => 'heroicon-o-no-symbol',
            self::SKIPPED => 'heroicon-o-arrow-right-circle',
            self::REQUEUED => 'heroicon-o-arrow-right',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SERVED => 'Served',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::SKIPPED => 'Skipped',
            self::REQUEUED => 'Requeued',
        };
    }
}
