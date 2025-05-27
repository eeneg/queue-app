<?php

use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::resource('services', ServiceController::class)->only(['index', 'show']);

Route::resource('tickets', TicketController::class)->only(['store', 'show']);
