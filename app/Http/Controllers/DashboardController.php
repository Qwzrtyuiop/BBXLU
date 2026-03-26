<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\User;
use App\Services\EventOverviewService;
use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, RankingService $rankingService, EventOverviewService $eventOverviewService): View
    {
        $activePanel = $request->string('panel')->toString();
        $selectedEventId = $request->integer('event') ?: null;

        if (! in_array($activePanel, ['overview', 'events', 'workspace', 'players'], true)) {
            $activePanel = 'overview';
        }

        $leaderboard = collect();
        $players = collect();
        $playersWithoutResults = collect();
        $registerableUsers = collect();

        if ($activePanel === 'overview') {
            $leaderboard = $rankingService->leaderboard(5);
            $registerableUsers = User::query()
                ->whereNotNull('nickname')
                ->orderByRaw('LOWER(nickname)')
                ->get(['id', 'nickname', 'is_claimed']);
        }

        if ($activePanel === 'players') {
            $leaderboard = $rankingService->leaderboard();
            $players = Player::query()
                ->with('user')
                ->orderBy('id')
                ->get();
            $playersWithoutResults = $rankingService->playersWithoutResults();
        }

        return view('dashboard', array_merge(
            $eventOverviewService->dashboardData($selectedEventId, $activePanel),
            [
                'activePanel' => $activePanel,
                'leaderboard' => $leaderboard,
                'players' => $players,
                'playersWithoutResults' => $playersWithoutResults,
                'registerableUsers' => $registerableUsers,
            ]
        ));
    }
}
