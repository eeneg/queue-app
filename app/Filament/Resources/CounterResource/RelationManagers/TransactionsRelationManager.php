<?php

namespace App\Filament\Resources\CounterResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

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
            ]);
    }
}
