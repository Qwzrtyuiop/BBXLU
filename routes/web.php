<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/players', [PlayerController::class, 'index'])->name('players.index');

    Route::resource('events', EventController::class)->except(['show'])->parameters([
        'events' => 'event',
    ]);
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

    Route::post('/events/{event}/participants', [EventController::class, 'storeParticipant'])->name('events.participants.store');
    Route::delete('/events/{event}/participants/{player}', [EventController::class, 'destroyParticipant'])->name('events.participants.destroy');

    Route::post('/events/{event}/results', [EventController::class, 'storeResult'])->name('events.results.store');
    Route::delete('/events/{event}/results/{result}', [EventController::class, 'destroyResult'])->name('events.results.destroy');

    Route::post('/events/{event}/awards', [EventController::class, 'storeAward'])->name('events.awards.store');
    Route::delete('/events/{event}/awards/{eventAward}', [EventController::class, 'destroyAward'])->name('events.awards.destroy');

    Route::post('/events/{event}/matches', [EventController::class, 'storeMatch'])->name('events.matches.store');
    Route::delete('/events/{event}/matches/{match}', [EventController::class, 'destroyMatch'])->name('events.matches.destroy');
});
