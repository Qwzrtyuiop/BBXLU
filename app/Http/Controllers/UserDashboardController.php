<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventAward;
use App\Models\EventParticipant;
use App\Models\EventResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserDashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role === 'admin') {
            return redirect()->route('dashboard');
        }

        $user->load('player');
        $player = $user->player;

        $stats = [
            'joined' => 0,
            'wins' => 0,
            'podiums' => 0,
            'awards' => 0,
        ];

        $upcomingEvents = collect();
        $recentResults = collect();
        $recentAwards = collect();

        if ($player) {
            $stats = [
                'joined' => EventParticipant::query()->where('player_id', $player->id)->count(),
                'wins' => EventResult::query()->where('player_id', $player->id)->where('placement', 1)->count(),
                'podiums' => EventResult::query()->where('player_id', $player->id)->whereBetween('placement', [1, 3])->count(),
                'awards' => EventAward::query()->where('player_id', $player->id)->count(),
            ];

            $upcomingEvents = Event::query()
                ->with(['eventType'])
                ->where('status', 'upcoming')
                ->whereHas('participants', fn ($query) => $query->where('players.id', $player->id))
                ->orderBy('date')
                ->orderBy('id')
                ->limit(6)
                ->get();

            $recentResults = EventResult::query()
                ->select('event_results.*')
                ->join('events', 'events.id', '=', 'event_results.event_id')
                ->with(['event.eventType'])
                ->where('event_results.player_id', $player->id)
                ->orderByDesc('events.date')
                ->orderByDesc('events.id')
                ->limit(6)
                ->get();

            $recentAwards = EventAward::query()
                ->select('event_awards.*')
                ->join('events', 'events.id', '=', 'event_awards.event_id')
                ->with(['award', 'event.eventType'])
                ->where('event_awards.player_id', $player->id)
                ->orderByDesc('events.date')
                ->orderByDesc('events.id')
                ->limit(6)
                ->get();
        }

        return view('user-dashboard', compact(
            'user',
            'player',
            'stats',
            'upcomingEvents',
            'recentResults',
            'recentAwards'
        ));
    }
}
