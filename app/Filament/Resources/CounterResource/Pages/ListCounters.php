<?php

namespace App\Filament\Resources\CounterResource\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\CounterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCounters extends ListRecords
{
    protected static string $resource = CounterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => Auth::user()->role === UserRole::ADMIN),
        ];
    }
}
