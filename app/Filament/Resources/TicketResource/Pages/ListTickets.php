<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Enums\LogStatus;
use App\Filament\Resources\TicketResource;
use App\Models\Service;
use App\Models\Ticket;
use Exception;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cancel-leftovers')
                ->requiresConfirmation()
                ->modalDescription('Cancel all leftover tickets from previous days')
                ->action(function (Actions\Action $component) {
                    try {
                        $component->beginDatabaseTransaction();

                        Ticket::query()
                            ->whereDate('created_at', '<', now())
                            ->where(function ($query) {
                                $query->whereDoesntHave('transaction')
                                    ->orWhereHas('transaction', fn ($query) => $query->whereRelation('log', 'status', LogStatus::SKIPPED));
                            })
                            ->lazyById()
                            ->each(function (Ticket $ticket) {
                                $transaction = $ticket->transaction()->create([
                                    'remarks' => 'Cancelled due to leftover',
                                    'user_id' => Auth::id(),
                                ]);

                                $transaction->log()->create([
                                    'status' => LogStatus::CANCELLED,
                                    'user_id' => Auth::id(),
                                ]);
                            });

                        $component->commitDatabaseTransaction();

                        $component->successNotificationTitle('Leftover tickets cancelled successfully');

                        $component->success();
                    } catch (Exception) {
                        $component->rollbackDatabaseTransaction();

                        $component->failureNotificationTitle('Failed to cancel leftover tickets');

                        $component->failure();
                    }
                }),
            Actions\CreateAction::make()
                ->modalWidth('lg')
                ->createAnother(false)
                ->mutateFormDataUsing(function (array $data): array {
                    $service = Service::find($data['service_id']);

                    $number = $service
                        ->tickets()
                        ->whereDate('created_at', now())
                        ->count() + 1;

                    $data['number'] = "{$service->prefix}-".str($number)->padLeft(3, '0');

                    return $data;
                }),
        ];
    }
}
