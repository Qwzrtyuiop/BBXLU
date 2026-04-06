<?php

namespace App\Http\Controllers;

use App\Services\EventOverviewService;
use App\Services\RankingService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(RankingService $rankingService, EventOverviewService $eventOverviewService): View
    {
        $leaderboard = $rankingService->leaderboard(10);
        $homeData = $eventOverviewService->homeData();
        $previewPlayerIds = $leaderboard
            ->pluck('player_id')
            ->merge(collect($homeData['awardLeaders'] ?? [])->pluck('player_id'))
            ->filter()
            ->unique()
            ->values();

        $leaderboardProfiles = $rankingService
            ->leaderboardWithAllPlayers()
            ->whereIn('player_id', $previewPlayerIds)
            ->values();

        return view('home', array_merge(
            $homeData,
            [
                'leaderboard' => $leaderboard,
                'leaderboardProfiles' => $rankingService->leaderboardProfilePreviews($leaderboardProfiles),
                'leaderboardScoreTooltip' => $rankingService->leaderboardScoreTooltip(),
            ]
        ));
    }
}
