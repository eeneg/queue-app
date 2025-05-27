<?php

namespace App\Providers;

use App\Models\Token;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Livewire\Notifications;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::automaticallyEagerLoadRelationships();

        Sanctum::usePersonalAccessTokenModel(Token::class);

        Notifications::verticalAlignment(VerticalAlignment::End);

        Notifications::alignment(Alignment::End);

        TextInput::configureUsing(fn (TextInput $component) => $component->maxLength(255));

        Select::configureUsing(fn (Select $component) => $component->native(false));
    }
}
