<?php

namespace App\Filament\Resources\CounterResource\Pages;

use App\Enums\LogStatus;
use App\Enums\UserRole;
use App\Filament\Resources\CounterResource;
use App\Models\Counter;
use App\Models\Ticket;
use App\Models\User;
use Exception;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Auth\Authenticatable;
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
            Actions\ActionGroup::make([
                Actions\Action::make('switch')
                    ->icon('heroicon-o-arrows-right-left')
                    ->modalIcon('heroicon-o-arrows-right-left')
                    ->modalDescription(
                        Auth::user()->role === UserRole::ADMIN
                            ? 'Switch counter to another user.'
                            : 'Switch the current user to another counter.'
                    )
                    ->modalWidth('lg')
                    ->visible(function (Authenticatable $authenticated, Counter $record) {
                        return $authenticated->role === UserRole::ADMIN ?:
                            $authenticated->id === $record->user_id;
                    })
                    ->form([
                        Select::make('counter_id')
                            ->label('Counter')
                            ->options(Counter::pluck('name', 'id'))
                            ->visible(fn (Authenticatable $authenticated) => $authenticated->role === UserRole::AGENT),
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name', modifyQueryUsing: fn ($query) => $query->where('role', UserRole::AGENT)->whereNot('id', $this->record->user_id))
                            ->preload()
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn (User $user) => "{$user->name} ({$user->username})")
                            ->visible(fn (Authenticatable $authenticated) => $authenticated->role === UserRole::ADMIN)
                            ->required(),
                    ])
                    ->action(function ($record, Actions\Action $component, array $data) {
                        if (Auth::user()->role === UserRole::ADMIN) {
                            try {
                                $component->beginDatabaseTransaction();

                                $previous = Counter::where($data)->first();

                                $previous?->update($record->only('user_id'));

                                $component->commitDatabaseTransaction();

                                $component->success();
                            } catch (Exception $ex) {
                                throw $ex;
                                $component->rollBackDatabaseTransaction();

                                $component->failure();
                            }

                            return;
                        }

                    })
                    ->successNotificationTitle('Counter switch successful.')
                    ->failureNotificationTitle('Counter switch failed.'),
            ]),
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

            usleep(500 * 1000);

            $component->success();
        } catch (Exception) {
            $component->rollBackDatabaseTransaction();

            $component->failureNotificationTitle('Failed to get next ticket.');

            $component->failure();
        }
    }
}
