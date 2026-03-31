<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function index(RankingService $rankingService): View
    {
        $leaderboard = $rankingService->leaderboardWithAllPlayers();

        return view('players.index', [
            'leaderboard' => $leaderboard,
            'leaderboardProfiles' => $rankingService->leaderboardProfilePreviews($leaderboard),
        ]);
    }
}
