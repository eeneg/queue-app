<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Service;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('lg')
                ->createAnother(false)
                ->mutateFormDataUsing(function (array $data): array {
                    $service = Service::find($data['service_id']);

                    $number = $service
                        ->tickets()
                        ->whereDate('created_at', now())
                        ->count() + 1;

                    $data['number'] = "{$service->prefix}-" . str($number)->padLeft(3, '0');

                    return $data;
                })
        ];
    }
}
