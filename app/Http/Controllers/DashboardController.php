<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Player;
use App\Models\User;
use App\Services\EventOverviewService;
use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $dashboardData = $eventOverviewService->dashboardData($selectedEventId, $activePanel);
        $leaderboard = collect();
        $players = collect();
        $playersWithoutResults = collect();
        $registerableUsers = collect();

        if ($activePanel === 'overview') {
            $leaderboard = $rankingService->leaderboard(5);
            $overviewEvent = $dashboardData['ongoingTournament']
                ?? $dashboardData['upcomingEvents']->first()
                ?? $dashboardData['selectedEvent']
                ?? $dashboardData['latestEvent'];
            $registerableUsers = $this->registerableUsersForEvent($overviewEvent);
        }

        if ($activePanel === 'workspace') {
            $registerableUsers = $this->registerableUsersForEvent($dashboardData['selectedEvent']);
        }

        if ($activePanel === 'players') {
            $leaderboard = $rankingService->leaderboard();
            $players = Player::query()
                ->with('user')
                ->get();
            $players = $players
                ->sortBy(fn (Player $player) => strtolower($player->user->nickname))
                ->values();
            $playersWithoutResults = $rankingService->playersWithoutResults();
        }

        return view('dashboard', array_merge(
            $dashboardData,
            [
                'activePanel' => $activePanel,
                'leaderboard' => $leaderboard,
                'players' => $players,
                'playersWithoutResults' => $playersWithoutResults,
                'registerableUsers' => $registerableUsers,
            ]
        ));
    }

    private function registerableUsersForEvent(?Event $event): Collection
    {
        if (! $event || $event->status !== 'upcoming' || $event->hasStarted()) {
            return collect();
        }

        return User::query()
            ->whereNotNull('nickname')
            ->whereDoesntHave('player.eventParticipations', function ($participantQuery) use ($event) {
                $participantQuery->where('event_id', $event->id);
            })
            ->orderByRaw('LOWER(nickname)')
            ->get(['id', 'nickname', 'is_claimed']);
    }
}
