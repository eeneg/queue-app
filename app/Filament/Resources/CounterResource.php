<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\CounterResource\Pages;
use App\Filament\Resources\CounterResource\RelationManagers;
use App\Models\Counter;
use App\Models\Ticket;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CounterResource extends Resource
{
    protected static ?string $model = Counter::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isGloballySearchable = false;

    public static function canAccess(): bool
    {
        return in_array(Auth::user()->role, [UserRole::ADMIN, UserRole::AGENT]);
    }

    public static function canView(Model $record): bool
    {
        $user = Auth::user();

        return $user->role === UserRole::ADMIN ?:
            $user->role === UserRole::AGENT && $record->user_id === $user->id;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()->role === UserRole::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->markAsRequired()
                    ->rule('required'),
                Forms\Components\TextInput::make('description'),
                Forms\Components\Select::make('user')
                    ->relationship('user', 'name', modifyQueryUsing: fn ($query) => $query->where('role', UserRole::AGENT))
                    ->preload()
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} ({$record->username})"),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable(['users.username', 'users.name'])
                    ->searchable(['user.name', 'user.username'])
                    ->placeholder('Vacant'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(2)
            ->schema([
                Infolists\Components\TextEntry::make('transaction.ticket.number')
                    ->label(str('&nbsp;')->toHtmlString())
                    ->alignCenter()
                    ->extraAttributes(['class' => 'font-mono'])
                    ->placeholder('No ticket')
                    ->columnSpanFull()
                    ->formatStateUsing(fn (string $state) => str("<span style='font-size: 7.5rem;'>$state</span>")
                        ->toHtmlString()
                    ),
                Infolists\Components\TextEntry::make('queued-tickets')
                    ->state(fn () => Ticket::queued()->pluck('number'))
                    ->extraAttributes(['class' => 'font-mono', 'wire:poll.5s' => ''])
                    ->placeholder('No tickets')
                    ->bulleted(),
                Infolists\Components\TextEntry::make('dailyTransactions.ticket.number')
                    ->extraAttributes(['class' => 'font-mono'])
                    ->placeholder('No transactions')
                    ->bulleted(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SkippedTransactionsRelationManager::class,
            RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCounters::route('/'),
            'create' => Pages\CreateCounter::route('/create'),
            'edit' => Pages\EditCounter::route('/{record}/edit'),
            'view' => Pages\ViewCounter::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'The number of counters';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(Auth::user()->role === UserRole::AGENT, fn ($query) => $query->where('user_id', Auth::id()))
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
