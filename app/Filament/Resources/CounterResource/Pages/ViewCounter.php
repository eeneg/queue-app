<?php

namespace App\Filament\Resources\CounterResource\Pages;

use App\Enums\LogStatus;
use App\Filament\Resources\CounterResource;
use App\Models\Ticket;
use Exception;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ViewCounter extends ViewRecord
{
    protected $listeners = ['refresh' => '$refresh'];

    protected static string $resource = CounterResource::class;

    public function getHeading(): string|Htmlable
    {
        return $this->record->name;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->record->user->name ?? str('<i>Vacant</i>')->toHtmlString();
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Counter';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('next')
                ->icon('heroicon-o-forward')
                ->keyBindings(['mod+space'])
                ->action(fn (Actions\Action $component) => $this->advanceTicket($component, LogStatus::COMPLETED))
                ->requiresConfirmation(),
            Actions\Action::make('skip')
                ->icon('heroicon-o-arrow-uturn-right')
                ->keyBindings(['mod+shift+space'])
                ->action(fn (Actions\Action $component) => $this->advanceTicket($component, LogStatus::SKIPPED))
                ->requiresConfirmation(),
        ];
    }

    protected function advanceTicket(Actions\Action $component, LogStatus $status)
    {
        $counter = $this->record;

        try {
            if ($counter->transaction) {
                $counter->transaction->logs()
                    ->create([
                        'status' => $status,
                        'user_id' => Auth::id(),
                    ]);
            }

            $ticket = Ticket::query()
                ->whereDoesntHave('transaction')
                ->orWhereHas('transaction', fn ($query) => $query->whereRelation('log', 'status', LogStatus::REQUEUED))
                ->first();

            if ($ticket === null) {
                $component->successNotificationTitle('No tickets left in the queue.');

                $component->success();

                return;
            }

            $component->beginDatabaseTransaction();

            $ticket->assign($counter);

            $component->commitDatabaseTransaction();

            $component->successNotificationTitle('Next ticket #'.$ticket->number);

            $this->dispatch('refresh');

            $component->success();
        } catch (Exception) {
            $component->rollBackDatabaseTransaction();

            $component->failureNotificationTitle('Failed to get next ticket.');

            $component->failure();
        }
    }
}
