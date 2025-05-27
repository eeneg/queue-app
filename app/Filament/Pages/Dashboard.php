<?php

namespace App\Filament\Pages;

use App\Models\Counter;
use App\Models\Ticket;
use Filament\Pages\Dashboard as Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

class Dashboard extends Page
{
    protected static string $view = 'filament.pages.dashboard';

    protected ?string $maxContentWidth = 'screen-2xl';

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    #[Computed]
    public function counters(): Collection
    {
        return Counter::query()
            ->active()
            ->occupied()
            ->get();
    }

    #[Computed]
    public function queue(): Collection
    {
        return Ticket::query()
            ->whereDoesntHave('transaction')
            ->get();
    }
}
