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
        return $this->leaderboardRows($limit);
    }

    public function leaderboardWithAllPlayers(): Collection
    {
        $rankedRows = $this->leaderboardRows();
        $rankedPlayerIds = $rankedRows->pluck('player_id');

        $unrankedRows = Player::query()
            ->with('user')
            ->whereDoesntHave('results')
            ->get()
            ->reject(fn (Player $player) => $rankedPlayerIds->contains($player->id))
            ->sortBy(fn (Player $player) => strtolower($player->user->nickname))
            ->values()
            ->map(function (Player $player) {
                return (object) [
                    'player_id' => $player->id,
                    'nickname' => $player->user->nickname,
                    'events_played' => 0,
                    'first_places' => 0,
                    'points' => 0,
                    'is_claimed' => (bool) $player->user->is_claimed,
                    'rank' => null,
                    'is_ranked' => false,
                ];
            });

        return $rankedRows
            ->concat($unrankedRows)
            ->values();
    }

    private function leaderboardRows(?int $limit = 50): Collection
    {
        $query = DB::table('event_results as er')
            ->join('events as e', 'e.id', '=', 'er.event_id')
            ->join('event_types as et', 'et.id', '=', 'e.event_type_id')
            ->join('players as p', 'p.id', '=', 'er.player_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->selectRaw('
                p.id as player_id,
                u.nickname,
                u.is_claimed,
                count(er.id) as events_played,
                sum(case when er.placement = 1 then 1 else 0 end) as first_places,
                sum(case
                    when et.name = "GT" then case er.placement when 1 then 12 when 2 then 9 when 3 then 7 when 4 then 5 else 0 end
                    when et.name = "Casual" then case er.placement when 1 then 8 when 2 then 6 when 3 then 4 when 4 then 2 else 0 end
                    else case er.placement when 1 then 6 when 2 then 4 when 3 then 3 when 4 then 1 else 0 end
                end) as points
            ')
            ->groupBy('p.id', 'u.nickname', 'u.is_claimed')
            ->orderByDesc('points')
            ->orderByDesc('first_places')
            ->orderBy('u.nickname');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $rows = $query->get();

        return $rows->values()->map(function ($row, int $index) {
            $row->rank = $index + 1;
            $row->is_ranked = true;
            return $row;
        });
    }

    public function playersWithoutResults(int $limit = 50): Collection
    {
        return Player::query()
            ->with('user')
            ->whereDoesntHave('results')
            ->get()
            ->sortBy(fn (Player $player) => strtolower($player->user->nickname))
            ->take($limit)
            ->values();
    }
}
