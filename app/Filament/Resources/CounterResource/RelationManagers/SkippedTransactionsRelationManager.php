<?php

namespace App\Filament\Resources\CounterResource\RelationManagers;

use App\Enums\LogStatus;
use App\Models\Transaction;
use Exception;
use Filament\Forms\Components\Radio;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SkippedTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'Skipped';

    protected $listeners = ['refresh' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn ($query) => $query->reorder()
                ->whereRelation('log', 'status', LogStatus::SKIPPED)
                ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('ticket.number')
                    ->label('Number')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('ticket.service.name')
                    ->label('Service')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Agent')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('ticket.created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('log.status')
                    ->label('Status')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('serve')
                    ->visible(fn (Transaction $record): bool => $record->log?->status === LogStatus::SKIPPED)
                    ->icon(LogStatus::SERVED->getIcon())
                    ->modalIcon(LogStatus::SERVED->getIcon())
                    ->modalDescription(fn (Transaction $record) => 'Serve ticket '.$record->ticket->number)
                    ->modalWidth('lg')
                    ->form([
                        Radio::make('current')
                            ->helperText(fn () => 'Current transaction '.$this->ownerRecord->transaction?->ticket->number)
                            ->markAsRequired()
                            ->rule('required')
                            ->hiddenLabel()
                            ->hidden(fn () => is_null($this->ownerRecord->transaction))
                            ->options([
                                LogStatus::SKIPPED->value => 'Skip',
                                LogStatus::COMPLETED->value => 'Complete',
                            ]),
                    ])
                    ->action(function (Tables\Actions\Action $component, Transaction $record, array $data) {
                        try {
                            $component->beginDatabaseTransaction();

                            if ($this->ownerRecord->transaction) {
                                $this->ownerRecord->transaction->log()->update([
                                    'status' => LogStatus::from($data['current']),
                                    'user_id' => Auth::id(),
                                ]);
                            }

                            $record->log()->create([
                                'status' => LogStatus::SERVED,
                                'user_id' => Auth::id(),
                            ]);

                            $component->commitDatabaseTransaction();

                            $component->successNotificationTitle('Serving ticket #'.$record->ticket->number);

                            $component->success();
                        } catch (Exception) {
                            $component->rollBackDatabaseTransaction();

                            $component->failureNotificationTitle('Transaction serve failed');

                            $component->failure();
                        }

                        $this->dispatch('refresh');
                    }),
            ]);
    }
}
