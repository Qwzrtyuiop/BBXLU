<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventResult;
use App\Models\Player;
use App\Models\User;
use App\Services\RankingService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(RankingService $rankingService): View
    {
        $stats = [
            'users' => User::count(),
            'players' => Player::count(),
            'events' => Event::count(),
        ];

        $events = Event::query()
            ->with(['eventType', 'creator'])
            ->withCount('participants')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $ongoingTournament = Event::query()
            ->where('status', 'upcoming')
            ->orderBy('date')
            ->orderBy('id')
            ->first();

        $ongoingTournamentLink = null;
        if ($ongoingTournament) {
            if (filter_var($ongoingTournament->challonge_url, FILTER_VALIDATE_URL)) {
                $ongoingTournamentLink = $ongoingTournament->challonge_url;
            } else {
                $description = (string) ($ongoingTournament->description ?? '');

                if (
                    preg_match('/https?:\/\/(?:www\.)?challonge\.com\/[^\s)]+/i', $description, $matches) === 1 ||
                    preg_match('/https?:\/\/[^\s)]+/i', $description, $matches) === 1
                ) {
                    $ongoingTournamentLink = rtrim($matches[0], '.,;!?)]');
                } elseif (filter_var($ongoingTournament->location, FILTER_VALIDATE_URL)) {
                    $ongoingTournamentLink = $ongoingTournament->location;
                }
            }
        }

        $latestEvent = Event::query()
            ->with(['eventType', 'creator'])
            ->withCount('participants')
            ->where('status', 'finished')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();
        $latestEventPlacements = collect();
        if ($latestEvent) {
            $latestEventPlacements = EventResult::query()
                ->with('player.user')
                ->where('event_id', $latestEvent->id)
                ->orderBy('placement')
                ->limit(4)
                ->get();
        }

        $leaderboard = $rankingService->leaderboard(10);

        $awardCategories = [
            [
                'title' => 'The G.O.A.T',
                'award_name' => 'Swiss Champ',
                'description' => 'most swiss champs',
            ],
            [
                'title' => 'King of Kings',
                'award_name' => 'Swiss King',
                'description' => 'most swiss kings',
            ],
            [
                'title' => 'Big Bird',
                'award_name' => 'Bird King',
                'description' => 'most bird kings',
            ],
        ];

        $awardLeaders = collect($awardCategories)->map(function (array $category) {
            $leader = DB::table('event_awards as ea')
                ->join('awards as a', 'a.id', '=', 'ea.award_id')
                ->join('players as p', 'p.id', '=', 'ea.player_id')
                ->join('users as u', 'u.id', '=', 'p.user_id')
                ->where('a.name', $category['award_name'])
                ->selectRaw('u.nickname, count(*) as total')
                ->groupBy('u.nickname')
                ->orderByDesc('total')
                ->orderBy('u.nickname')
                ->first();

            return [
                'title' => $category['title'],
                'description' => $category['description'],
                'award_name' => $category['award_name'],
                'nickname' => $leader?->nickname,
                'total' => $leader?->total ?? 0,
            ];
        });

        return view('home', compact(
            'stats',
            'events',
            'ongoingTournament',
            'ongoingTournamentLink',
            'latestEvent',
            'latestEventPlacements',
            'leaderboard',
            'awardLeaders'
        ));
    }
}
