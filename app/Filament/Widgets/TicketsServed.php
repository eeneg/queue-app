<?php

namespace App\Filament\Widgets;

use App\Models\Counter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketsServed extends BaseWidget
{
    protected static ?string $pollingInterval = '1s';

    protected ?string $heading = 'Counters';

    protected function getStats(): array
    {
        return Counter::query()
            ->active()
            ->with('transaction.ticket')
            ->lazyById()
            ->map(function (Counter $counter) {
                return Stat::make($counter->name, $counter->transaction?->ticket->number ?? 'Idle')
                    ->description($counter->user?->name ?? '')
                    ->color('success');
            })
            ->toArray();
    }
}
