<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Player;
use App\Models\User;
use App\Services\RankingService;

class DashboardController extends Controller
{
    public function __invoke(RankingService $rankingService)
    {
        $stats = [
            'users' => User::count(),
            'players' => Player::count(),
            'events' => Event::count(),
            'upcoming' => Event::where('status', 'upcoming')->count(),
        ];

        $upcomingEvents = Event::query()
            ->with(['eventType', 'creator'])
            ->where('status', 'upcoming')
            ->orderBy('date')
            ->limit(5)
            ->get();

        $recentEvents = Event::query()
            ->with(['eventType', 'creator'])
            ->latest('date')
            ->limit(5)
            ->get();

        $leaderboard = $rankingService->leaderboard(10);

        return view('dashboard', compact('stats', 'upcomingEvents', 'recentEvents', 'leaderboard'));
    }
}
