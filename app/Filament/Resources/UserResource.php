<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->alphaNum()
                    ->markAsRequired()
                    ->rule('required')
                    ->dehydrateStateUsing(fn ($state) => strtolower($state))
                    ->extraInputAttributes(['style' => 'text-transform: lowercase']),
                Forms\Components\TextInput::make('name')
                    ->markAsRequired()
                    ->rule('required'),
                Forms\Components\Select::make('role')
                    ->options(UserRole::class)
                    ->required(),
                ...static::passwordFormComponents(),
            ]);
    }

    public static function passwordFormComponents(bool $onCreateOnly = true): array
    {
        return [
            Forms\Components\TextInput::make('password')
                ->password()
                ->markAsRequired()
                ->rule('required')
                ->confirmed()
                ->hiddenOn($onCreateOnly ? ['edit'] : [])
                ->revealable(filament()->arePasswordsRevealable()),
            Forms\Components\TextInput::make('password_confirmation')
                ->password()
                ->rule('required')
                ->dehydrated(false)
                ->hiddenOn($onCreateOnly ? ['edit'] : [])
                ->revealable(filament()->arePasswordsRevealable()),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('username');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'The number of users';
    }
}
