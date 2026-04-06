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
    private const BAYESIAN_PRIOR_MATCHES = 8.0;

    private const FINISH_RANKING_POINTS = [
        'spin' => 0.5,
        'burst' => 1.0,
        'over' => 1.0,
        'extreme' => 1.25,
    ];

    private const EVENT_TYPE_MULTIPLIERS = [
        'Casual' => 0.5,
        'GT' => 1.0,
    ];

    /**
     * Build leaderboard points from completed match finishes + event type multipliers.
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
                    'score_display' => $this->formatLeaderboardScore(0),
                    'raw_points' => 0,
                    'raw_points_display' => $this->formatLeaderboardScore(0),
                    'weighted_matches' => 0,
                    'weighted_matches_display' => $this->formatLeaderboardScore(0),
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
                    'points' => (float) $row->points,
                    'score_display' => $row->score_display ?? $this->formatLeaderboardScore((float) $row->points),
                    'raw_points' => (float) ($row->raw_points ?? 0),
                    'raw_points_display' => $row->raw_points_display ?? $this->formatLeaderboardScore((float) ($row->raw_points ?? 0)),
                    'weighted_matches' => (float) ($row->weighted_matches ?? 0),
                    'weighted_matches_display' => $row->weighted_matches_display ?? $this->formatLeaderboardScore((float) ($row->weighted_matches ?? 0)),
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
        $rows = DB::table('event_results as er')
            ->join('players as p', 'p.id', '=', 'er.player_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->selectRaw('
                p.id as player_id,
                u.nickname,
                u.is_claimed,
                count(er.id) as events_played,
                sum(case when er.placement = 1 then 1 else 0 end) as first_places
            ')
            ->groupBy('p.id', 'u.nickname', 'u.is_claimed')
            ->get();

        $scoreMetrics = $this->leaderboardScoreMetrics(
            $rows->pluck('player_id')
                ->map(fn ($playerId) => (int) $playerId)
                ->all()
        );

        $rows = $rows
            ->map(function ($row) use ($scoreMetrics) {
                $metrics = $scoreMetrics[(int) $row->player_id] ?? $this->defaultScoreMetrics();
                $row->points = $metrics['score'];
                $row->score_display = $metrics['score_display'];
                $row->raw_points = $metrics['raw_points'];
                $row->raw_points_display = $metrics['raw_points_display'];
                $row->weighted_matches = $metrics['weighted_matches'];
                $row->weighted_matches_display = $metrics['weighted_matches_display'];

                return $row;
            })
            ->sort(function ($left, $right) {
                foreach (['points', 'first_places'] as $field) {
                    $comparison = $right->{$field} <=> $left->{$field};
                    if ($comparison !== 0) {
                        return $comparison;
                    }
                }

                return strcasecmp($left->nickname, $right->nickname);
            })
            ->values();

        if ($limit !== null) {
            $rows = $rows->take($limit)->values();
        }

        return $rows->map(function ($row, int $index) {
            $row->rank = $index + 1;
            $row->is_ranked = true;

            return $row;
        });
    }

    public function leaderboardScoreTooltip(): string
    {
        $priorMatches = $this->formatLeaderboardScore(self::BAYESIAN_PRIOR_MATCHES);

        return "Score = (your weighted points + league average x {$priorMatches} sample matches) / (your weighted matches + {$priorMatches}). Weighted points: Spin 0.5, Burst 1, Over 1, Extreme 1.25. Casual matches count half for both points and sample size.";
    }

    private function leaderboardScoreMetrics(array $playerIds): array
    {
        if ($playerIds === []) {
            return [];
        }

        $metrics = collect($playerIds)
            ->mapWithKeys(fn (int $playerId) => [$playerId => $this->defaultScoreMetrics()])
            ->all();

        $matches = EventMatch::query()
            ->with(['event.eventType:id,name'])
            ->whereHas('event', fn ($query) => $query->where('status', 'finished'))
            ->where('status', 'completed')
            ->whereNotNull('player2_id')
            ->where('is_bye', false)
            ->where(function ($query) use ($playerIds): void {
                $query->whereIn('player1_id', $playerIds)
                    ->orWhereIn('player2_id', $playerIds);
            })
            ->get();

        foreach ($matches as $match) {
            $eventMultiplier = $this->eventTypeRankingMultiplier($match->event?->eventType?->name);
            $player1Id = (int) $match->player1_id;
            $player2Id = (int) $match->player2_id;

            if (array_key_exists($player1Id, $metrics)) {
                $metrics[$player1Id]['weighted_matches'] += $eventMultiplier;
            }

            if ($player2Id && array_key_exists($player2Id, $metrics)) {
                $metrics[$player2Id]['weighted_matches'] += $eventMultiplier;
            }

            $winnerId = (int) $match->winner_id;
            if (! $winnerId || ! array_key_exists($winnerId, $metrics)) {
                continue;
            }

            $basePoints = $match->battleResults()->sum(fn (array $battle) => $this->finishRankingPoints($battle['type'] ?? null));
            $metrics[$winnerId]['raw_points'] += $basePoints * $eventMultiplier;
        }

        $leagueRawPoints = collect($metrics)->sum('raw_points');
        $leagueWeightedMatches = collect($metrics)->sum('weighted_matches');
        $leagueAverage = $leagueWeightedMatches > 0 ? ($leagueRawPoints / $leagueWeightedMatches) : 0.0;

        foreach ($metrics as $playerId => $playerMetrics) {
            $weightedMatches = (float) $playerMetrics['weighted_matches'];
            $rawPoints = (float) $playerMetrics['raw_points'];

            $score = $weightedMatches > 0
                ? (($rawPoints + ($leagueAverage * self::BAYESIAN_PRIOR_MATCHES)) / ($weightedMatches + self::BAYESIAN_PRIOR_MATCHES))
                : 0.0;

            $metrics[$playerId]['score'] = round($score, 3);
            $metrics[$playerId]['score_display'] = $this->formatLeaderboardScore($metrics[$playerId]['score']);
            $metrics[$playerId]['raw_points'] = round($rawPoints, 3);
            $metrics[$playerId]['raw_points_display'] = $this->formatLeaderboardScore($metrics[$playerId]['raw_points']);
            $metrics[$playerId]['weighted_matches'] = round($weightedMatches, 3);
            $metrics[$playerId]['weighted_matches_display'] = $this->formatLeaderboardScore($metrics[$playerId]['weighted_matches']);
        }

        return $metrics;
    }

    private function finishRankingPoints(?string $finishType): float
    {
        return (float) (self::FINISH_RANKING_POINTS[$finishType] ?? self::FINISH_RANKING_POINTS['spin']);
    }

    private function eventTypeRankingMultiplier(?string $eventTypeName): float
    {
        return (float) (self::EVENT_TYPE_MULTIPLIERS[$eventTypeName] ?? 1.0);
    }

    private function defaultScoreMetrics(): array
    {
        return [
            'score' => 0.0,
            'score_display' => $this->formatLeaderboardScore(0),
            'raw_points' => 0.0,
            'raw_points_display' => $this->formatLeaderboardScore(0),
            'weighted_matches' => 0.0,
            'weighted_matches_display' => $this->formatLeaderboardScore(0),
        ];
    }

    private function formatLeaderboardScore(float|int $value): string
    {
        $formatted = number_format((float) $value, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
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
