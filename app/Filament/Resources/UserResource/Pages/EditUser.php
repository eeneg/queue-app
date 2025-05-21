<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?UserRole $role = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Password')
                ->icon('heroicon-o-key')
                ->form(static::getResource()::passwordFormComponents(onCreateOnly: false))
                ->requiresConfirmation()
                ->modalDescription('You are about to change the password for this user. Please ensure that you have the new password ready.')
                ->modalWidth('lg')
                ->modalAlignment('left')
                ->slideOver(),
            Actions\ActionGroup::make([
                Actions\DeleteAction::make(),
            ]),
        ];
    }

    protected function beforeSave(): void
    {
        $this->role = $this->record->getOriginal('role');
    }

    protected function afterSave(): void
    {
        if ($this->record->wasChanged('role') && $this->role === UserRole::AGENT) {
            $this->record->counter()->update(['user_id' => null]);
        }

        $this->role = null;
    }
}
