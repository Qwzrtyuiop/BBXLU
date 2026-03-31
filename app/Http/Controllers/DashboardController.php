<?php

namespace App\Http\Controllers;

use App\Models\Event;
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
        $leaderboardProfiles = collect();
        $players = collect();
        $playersWithoutResults = collect();
        $registerableUsers = collect();
        $playerRegistrationEvent = null;

        if ($activePanel === 'overview') {
            $leaderboard = $rankingService->leaderboard(10);
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
            $leaderboard = $rankingService->leaderboardWithAllPlayers();
            $leaderboardProfiles = $rankingService->leaderboardProfilePreviews($leaderboard);
            $playersWithoutResults = $rankingService->playersWithoutResults();
            $playerRegistrationEvent = Event::query()
                ->with('eventType')
                ->where('status', 'upcoming')
                ->orderByRaw("CASE WHEN is_active = 1 THEN 0 ELSE 1 END")
                ->orderBy('date')
                ->orderBy('id')
                ->get()
                ->first(fn (Event $event) => ! $event->hasStarted() && (! $event->usesLockedDecks() || $event->is_active));
            $registerableUsers = $this->registerableUsersForEvent($playerRegistrationEvent);
        }

        return view('dashboard', array_merge(
            $dashboardData,
            [
                'activePanel' => $activePanel,
                'leaderboard' => $leaderboard,
                'leaderboardProfiles' => $leaderboardProfiles,
                'players' => $players,
                'playersWithoutResults' => $playersWithoutResults,
                'registerableUsers' => $registerableUsers,
                'playerRegistrationEvent' => $playerRegistrationEvent,
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
