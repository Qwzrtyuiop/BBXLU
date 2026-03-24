<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Services\RankingService;

class PlayerController extends Controller
{
    public function index(RankingService $rankingService)
    {
        $leaderboard = $rankingService->leaderboard();
        $players = Player::query()
            ->with('user')
            ->orderBy('id')
            ->get();
        $playersWithoutResults = $rankingService->playersWithoutResults();

        return view('players.index', compact('leaderboard', 'players', 'playersWithoutResults'));
    }
}
