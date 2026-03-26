<?php

namespace App\Http\Controllers;

use App\Services\EventOverviewService;
use App\Services\RankingService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(RankingService $rankingService, EventOverviewService $eventOverviewService): View
    {
        return view('home', array_merge(
            $eventOverviewService->homeData(),
            ['leaderboard' => $rankingService->leaderboard(10)]
        ));
    }
}
