<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LiveViewerController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
Route::get('/live', [LiveViewerController::class, 'index'])->name('live.viewer');
Route::get('/live/match/{match}', [LiveViewerController::class, 'showMatch'])
    ->whereNumber('match')
    ->name('live.viewer.match');
Route::get('/live/{event}', [LiveViewerController::class, 'showEvent'])
    ->whereNumber('event')
    ->name('live.viewer.event');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegister'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/userdashboard', UserDashboardController::class)->name('user.dashboard');
});

Route::get('/userdashboard/{player}', [UserDashboardController::class, 'show'])
    ->whereNumber('player')
    ->name('user.dashboard.profile');

Route::middleware(['auth', 'admin'])->group(function (): void {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('events', EventController::class)->except(['show'])->parameters([
        'events' => 'event',
    ]);
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

    Route::post('/events/{event}/participants', [EventController::class, 'storeParticipant'])->name('events.participants.store');
    Route::post('/events/{event}/participants/{player}/deck', [EventController::class, 'updateParticipantDeck'])->name('events.participants.deck.store');
    Route::post('/events/{event}/participants/decks/bulk', [EventController::class, 'bulkUpdateParticipantDecks'])->name('events.participants.decks.bulk.store');
    Route::delete('/events/{event}/participants/{player}', [EventController::class, 'destroyParticipant'])->name('events.participants.destroy');

    Route::post('/events/{event}/activate', [EventController::class, 'activate'])->name('events.activate');
    Route::post('/events/{event}/live', [EventController::class, 'toggleLive'])->name('events.live.toggle');
    Route::post('/events/{event}/matches', [EventController::class, 'storeMatch'])->name('events.matches.store');
    Route::delete('/events/{event}/matches/{match}', [EventController::class, 'destroyMatch'])->name('events.matches.destroy');
    Route::post('/events/{event}/bracket/generate', [EventController::class, 'generateBracketRound'])->name('events.bracket.generate');
    Route::post('/events/{event}/outcomes/regenerate', [EventController::class, 'regenerateAutomaticResultsAndAwards'])->name('events.outcomes.regenerate');
});
