<?php

namespace App\Services;

use App\Models\EventAward;
use App\Models\EventMatch;
use App\Models\EventParticipant;
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

    public function leaderboardProfilePreviews(Collection $leaderboard): Collection
    {
        $playerIds = $leaderboard
            ->pluck('player_id')
            ->filter()
            ->values();

        if ($playerIds->isEmpty()) {
            return collect();
        }

        $players = Player::query()
            ->with('user')
            ->whereIn('id', $playerIds)
            ->get()
            ->keyBy('id');

        $podiumCounts = DB::table('event_results')
            ->selectRaw('player_id, count(*) as total')
            ->whereIn('player_id', $playerIds)
            ->whereBetween('placement', [1, 3])
            ->groupBy('player_id')
            ->pluck('total', 'player_id');

        $awardCounts = EventAward::query()
            ->selectRaw('player_id, count(*) as total')
            ->whereIn('player_id', $playerIds)
            ->groupBy('player_id')
            ->pluck('total', 'player_id');

        $participantRows = EventParticipant::query()
            ->whereIn('player_id', $playerIds)
            ->get();

        $mostUsedBeys = $participantRows
            ->groupBy('player_id')
            ->map(function (Collection $rows): array {
                $usage = collect();

                foreach ($rows as $participant) {
                    foreach ([$participant->deck_bey1, $participant->deck_bey2, $participant->deck_bey3] as $bey) {
                        if (! filled($bey)) {
                            continue;
                        }

                        $normalized = trim((string) $bey);
                        $usage->put($normalized, ((int) $usage->get($normalized, 0)) + 1);
                    }
                }

                $name = $usage->sortDesc()->keys()->first();

                return [
                    'name' => $name,
                    'count' => $name ? (int) $usage->get($name, 0) : 0,
                ];
            });

        $matchStats = $playerIds->mapWithKeys(fn ($playerId) => [
            (int) $playerId => [
                'wins' => 0,
                'losses' => 0,
                'matches' => 0,
                'score_sum' => 0,
                'finish_counts' => [
                    'spin' => 0,
                    'burst' => 0,
                    'over' => 0,
                    'extreme' => 0,
                ],
            ],
        ]);

        $matches = EventMatch::query()
            ->where('status', 'completed')
            ->whereNotNull('player2_id')
            ->where('is_bye', false)
            ->where(function ($query) use ($playerIds): void {
                $query->whereIn('player1_id', $playerIds)
                    ->orWhereIn('player2_id', $playerIds);
            })
            ->get();

        foreach ($matches as $match) {
            foreach ([
                ['id' => (int) $match->player1_id, 'score' => (int) $match->player1_score],
                ['id' => (int) $match->player2_id, 'score' => (int) $match->player2_score],
            ] as $side) {
                if (! $matchStats->has($side['id'])) {
                    continue;
                }

                $stats = $matchStats->get($side['id']);
                $stats['matches']++;
                $stats['score_sum'] += $side['score'];

                if ((int) $match->winner_id === $side['id']) {
                    $stats['wins']++;
                } else {
                    $stats['losses']++;
                }

                $matchStats->put($side['id'], $stats);
            }

            foreach ($match->battleResults() as $battle) {
                $finishType = (string) ($battle['type'] ?? 'spin');
                $winnerSlot = (int) ($battle['winner'] ?? 0);
                $winnerPlayerId = $winnerSlot === 1
                    ? (int) $match->player1_id
                    : ($winnerSlot === 2 ? (int) $match->player2_id : 0);

                if (! $winnerPlayerId || ! $matchStats->has($winnerPlayerId)) {
                    continue;
                }

                $stats = $matchStats->get($winnerPlayerId);

                if (! array_key_exists($finishType, $stats['finish_counts'])) {
                    continue;
                }

                $stats['finish_counts'][$finishType]++;
                $matchStats->put($winnerPlayerId, $stats);
            }
        }

        return $leaderboard->mapWithKeys(function ($row) use ($players, $podiumCounts, $awardCounts, $mostUsedBeys, $matchStats): array {
            $playerId = (int) $row->player_id;
            $player = $players->get($playerId);
            $stats = $matchStats->get($playerId, [
                'wins' => 0,
                'losses' => 0,
                'matches' => 0,
                'score_sum' => 0,
                'finish_counts' => [
                    'spin' => 0,
                    'burst' => 0,
                    'over' => 0,
                    'extreme' => 0,
                ],
            ]);
            $mostUsed = $mostUsedBeys->get($playerId, ['name' => null, 'count' => 0]);
            $bestFinish = collect($stats['finish_counts'])
                ->sortDesc()
                ->keys()
                ->first(fn (string $type) => ((int) ($stats['finish_counts'][$type] ?? 0)) > 0);

            return [
                $playerId => [
                    'player_id' => $playerId,
                    'nickname' => $row->nickname,
                    'name' => $player?->user?->name ?: $row->nickname,
                    'rank' => $row->rank !== null ? (int) $row->rank : null,
                    'is_ranked' => (bool) ($row->is_ranked ?? true),
                    'points' => (int) $row->points,
                    'events_played' => (int) $row->events_played,
                    'first_places' => (int) $row->first_places,
                    'podiums' => (int) ($podiumCounts[$playerId] ?? 0),
                    'awards' => (int) ($awardCounts[$playerId] ?? 0),
                    'is_claimed' => (bool) $row->is_claimed,
                    'match_wins' => (int) $stats['wins'],
                    'match_losses' => (int) $stats['losses'],
                    'win_rate' => (int) $stats['matches'] > 0
                        ? round(((int) $stats['wins'] / (int) $stats['matches']) * 100, 1)
                        : null,
                    'avg_score' => (int) $stats['matches'] > 0
                        ? round(((int) $stats['score_sum'] / (int) $stats['matches']), 1)
                        : null,
                    'most_used_bey' => $mostUsed['name'],
                    'most_used_bey_count' => (int) ($mostUsed['count'] ?? 0),
                    'best_finish' => $bestFinish,
                ],
            ];
        });
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
