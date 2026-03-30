<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventMatch;
use App\Services\EventOverviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LiveViewerController extends Controller
{
    public function index(EventOverviewService $eventOverviewService): View|RedirectResponse
    {
        $activeEvent = $eventOverviewService->activeLiveEvent();

        if ($activeEvent) {
            return redirect()->route('live.viewer.event', $activeEvent);
        }

        return view('live-viewer', [
            'ongoingTournament' => null,
            'ongoingTournamentPreview' => [],
        ]);
    }

    public function showEvent(Event $event, EventOverviewService $eventOverviewService): View
    {
        return view('live-viewer', $eventOverviewService->liveEventData($event));
    }

    public function showMatch(EventMatch $match, EventOverviewService $eventOverviewService): View
    {
        return view('live-match', $eventOverviewService->liveMatchData($match));
    }
}
