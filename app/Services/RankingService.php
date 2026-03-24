<?php

namespace App\Services;

use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class RankingService
{
    /**
     * Build leaderboard points from event_results + event_types.
     */
    public function leaderboard(int $limit = 50): Collection
    {
        $rows = DB::table('event_results as er')
            ->join('events as e', 'e.id', '=', 'er.event_id')
            ->join('event_types as et', 'et.id', '=', 'e.event_type_id')
            ->join('players as p', 'p.id', '=', 'er.player_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->selectRaw('
                p.id as player_id,
                u.nickname,
                count(er.id) as events_played,
                sum(case when er.placement = 1 then 1 else 0 end) as first_places,
                sum(case
                    when et.name = "GT" then case er.placement when 1 then 12 when 2 then 9 when 3 then 7 when 4 then 5 else 0 end
                    when et.name = "Casual" then case er.placement when 1 then 8 when 2 then 6 when 3 then 4 when 4 then 2 else 0 end
                    else case er.placement when 1 then 6 when 2 then 4 when 3 then 3 when 4 then 1 else 0 end
                end) as points
            ')
            ->groupBy('p.id', 'u.nickname')
            ->orderByDesc('points')
            ->orderByDesc('first_places')
            ->orderBy('u.nickname')
            ->limit($limit)
            ->get();

        return $rows->values()->map(function ($row, int $index) {
            $row->rank = $index + 1;
            return $row;
        });
    }

    public function playersWithoutResults(int $limit = 50): Collection
    {
        return Player::query()
            ->with('user')
            ->whereDoesntHave('results')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }
}
