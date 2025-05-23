<?php

namespace App\Filament\Resources\CounterResource\RelationManagers;

use App\Enums\LogStatus;
use App\Models\Transaction;
use Filament\Forms\Components\Radio;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected $listeners = ['refresh' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn ($query) => $query->reorder()->orderBy('created_at', 'desc'))
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
                    ->visible(fn (Transaction $record): bool => $record->log?->status === LogStatus::SKIPPED && now()->isSameDay($record->ticket->created_at))
                    ->icon(LogStatus::SERVED->getIcon())
                    ->modalIcon(LogStatus::SERVED->getIcon())
                    ->modalDescription(fn (Transaction $record) => 'Serve transaction '.$record->ticket->number)
                    ->modalWidth('lg')
                    ->form([
                        Radio::make('current')
                            ->helperText('Current transaction')
                            ->markAsRequired()
                            ->rule('required')
                            ->hidden(fn () => is_null($this->ownerRecord->transaction))
                            ->options([
                                LogStatus::SKIPPED->value => 'Skip',
                                LogStatus::COMPLETED->value => 'Complete',
                            ]),
                    ])
                    ->action(function (Transaction $record, array $data) {
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

                        $this->dispatch('refresh');
                    }),
            ]);
    }
}
