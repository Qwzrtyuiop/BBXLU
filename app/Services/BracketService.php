<?php

namespace App\Services;

use App\Models\Award;
use App\Models\Event;
use App\Models\EventAward;
use App\Models\EventMatch;
use App\Models\EventParticipant;
use App\Models\EventResult;
use App\Models\EventRound;
use App\Models\Player;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BracketService
{
    public function generateNextRound(Event $event, bool $reshuffle = false): string
    {
        return DB::transaction(function () use ($event, $reshuffle): string {
            $event = Event::query()
                ->with([
                    'participants.user',
                    'eventParticipants.player.user',
                    'rounds.matches.winner',
                    'matches',
                ])
                ->findOrFail($event->id);

            if ($event->participants->isEmpty()) {
                throw new RuntimeException('Add participants before generating a bracket round.');
            }

            if ($event->usesLockedDecks()) {
                $missingDeckParticipants = $this->deckRegistrationTargets($event)
                    ->filter(fn (EventParticipant $participant) => ! $participant->hasRegisteredDeck())
                    ->values();

                if ($missingDeckParticipants->isNotEmpty()) {
                    throw new RuntimeException(
                        'Register locked decks for all participants before generating the next round.'
                    );
                }
            }

            if ($event->bracket_type === 'single_elim') {
                if ($reshuffle) {
                    throw new RuntimeException('Reshuffle is only available for the first Swiss round.');
                }

                return $this->generateSingleEliminationRound($event);
            }

            return $this->generateSwissThenTopCutRound($event, $reshuffle);
        });
    }

    public function swissStandings(Event $event): Collection
    {
        $players = $event->participants()
            ->with('user')
            ->get()
            ->sortBy(fn (Player $player) => strtolower($player->user->nickname))
            ->values();

        $matches = $event->matches()
            ->where('stage', 'swiss')
            ->where('status', 'completed')
            ->get();

        $rows = $players->map(function (Player $player) use ($matches) {
            $playerMatches = $matches->filter(function (EventMatch $match) use ($player) {
                return $match->player1_id === $player->id || $match->player2_id === $player->id;
            });

            $wins = $playerMatches
                ->where('winner_id', $player->id)
                ->reject(fn (EventMatch $match) => $match->is_bye)
                ->count();
            $losses = $playerMatches
                ->reject(fn (EventMatch $match) => $match->is_bye)
                ->reject(fn (EventMatch $match) => $match->winner_id === $player->id)
                ->count();
            $byes = $playerMatches
                ->filter(fn (EventMatch $match) => $match->is_bye && $match->winner_id === $player->id)
                ->count();

            $battleWins = $playerMatches->sum(fn (EventMatch $match) => $match->weightedBattlePointsForPlayer($player->id));

            $battleLosses = $playerMatches->sum(function (EventMatch $match) use ($player) {
                $opponentId = $match->player1_id === $player->id
                    ? $match->player2_id
                    : $match->player1_id;

                return $opponentId ? $match->weightedBattlePointsForPlayer($opponentId) : 0;
            });

            $opponentIds = $playerMatches
                ->reject(fn (EventMatch $match) => $match->is_bye || ! $match->player2_id)
                ->map(function (EventMatch $match) use ($player) {
                    return $match->player1_id === $player->id ? $match->player2_id : $match->player1_id;
                })
                ->filter()
                ->values();

            $history = $playerMatches
                ->sortBy('round_number')
                ->map(function (EventMatch $match) use ($player) {
                    if ($match->is_bye) {
                        return 'B';
                    }

                    return $match->winner_id === $player->id ? 'W' : 'L';
                })
                ->values();

            return [
                'player' => $player,
                'wins' => $wins,
                'losses' => $losses,
                'ties' => 0,
                'byes' => $byes,
                'match_points' => $wins + $byes,
                'battle_points' => $battleWins,
                'points_diff' => $battleWins - $battleLosses,
                'buchholz' => 0,
                'opponent_ids' => $opponentIds,
                'history' => $history,
            ];
        });

        $matchPoints = $rows->mapWithKeys(fn (array $row) => [$row['player']->id => $row['match_points']]);

        $rows = $rows->map(function (array $row) use ($matchPoints) {
            $row['buchholz'] = $row['opponent_ids']->sum(fn (int $opponentId) => (int) ($matchPoints[$opponentId] ?? 0));

            return $row;
        });

        $sorted = $rows->sort(function (array $left, array $right) {
            foreach (['match_points', 'points_diff', 'battle_points', 'buchholz'] as $field) {
                $comparison = $right[$field] <=> $left[$field];
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return strcasecmp($left['player']->user->nickname, $right['player']->user->nickname);
        })->values();

        return $sorted->map(function (array $row, int $index) {
            $row['rank'] = $index + 1;

            return $row;
        });
    }

    public function refreshRoundStatus(EventRound $round): void
    {
        $hasPending = $round->matches()->where('status', 'pending')->exists();
        $round->status = $hasPending ? 'pending' : 'completed';
        $round->save();
    }

    public function refreshEventStatus(Event $event): void
    {
        $event->loadMissing([
            'participants.user',
            'rounds.matches',
        ]);

        $singleElimRounds = $event->rounds
            ->where('stage', 'single_elim')
            ->sortBy('round_number')
            ->values();

        if ($singleElimRounds->isNotEmpty()) {
            $finalRound = $singleElimRounds->last();
            $completed = $finalRound->status === 'completed' && $finalRound->matches->count() === 1;

            $event->bracket_status = $completed ? 'completed' : 'in_progress';
            $event->status = $completed ? 'finished' : 'upcoming';
            if ($completed) {
                $event->is_active = false;
            }
            $event->save();

            if ($completed) {
                $this->syncAutomaticResultsAndAwards($event);
            } else {
                $this->clearAutomaticResultsAndAwards($event);
            }

            return;
        }

        $event->bracket_status = $event->rounds->isEmpty() ? 'draft' : 'in_progress';
        if ($event->status === 'finished') {
            $event->status = 'upcoming';
        }
        $event->save();
        $this->clearAutomaticResultsAndAwards($event);
    }

    public function advanceSwissAfterRoundCompletion(EventRound $round): ?string
    {
        if ($round->stage !== 'swiss') {
            return null;
        }

        $event = Event::query()
            ->with([
                'participants.user',
                'rounds.matches.winner',
                'matches',
            ])
            ->findOrFail($round->event_id);

        if (! $event->usesSwissBracket()) {
            return null;
        }

        $latestSwissRound = $event->rounds
            ->where('stage', 'swiss')
            ->sortBy('round_number')
            ->last();

        if (! $latestSwissRound || $latestSwissRound->id !== $round->id || $round->status !== 'completed') {
            return null;
        }

        $hasFutureSwissRound = $event->rounds
            ->where('stage', 'swiss')
            ->contains(fn (EventRound $eventRound) => $eventRound->round_number > $round->round_number);

        if ($hasFutureSwissRound) {
            return null;
        }

        $hasTopCut = $event->rounds->where('stage', 'single_elim')->isNotEmpty();
        $shouldAdvance = $round->round_number < (int) $event->swiss_rounds || ! $hasTopCut;

        if (! $shouldAdvance) {
            return null;
        }

        return $this->generateNextRound($event);
    }

    public function canReshuffleFirstSwissRound(Event $event): bool
    {
        if (! $event->usesSwissBracket()) {
            return false;
        }

        $event->loadMissing('rounds.matches');

        $swissRounds = $event->rounds
            ->where('stage', 'swiss')
            ->sortBy('round_number')
            ->values();

        if ($swissRounds->isEmpty() || $swissRounds->count() > 1) {
            return false;
        }

        $roundOne = $swissRounds->first();

        return (int) $roundOne->round_number === 1
            && $roundOne->matches->every(fn (EventMatch $match) => $match->status === 'pending');
    }

    public function deckRegistrationTargets(Event $event): Collection
    {
        $event->loadMissing([
            'participants.user',
            'eventParticipants.player.user',
            'rounds.matches',
        ]);

        $participants = $event->eventParticipants
            ->sortBy(fn (EventParticipant $participant) => strtolower($participant->player->user->nickname))
            ->values();

        if ($participants->isEmpty()) {
            return collect();
        }

        if ($event->usesLockedDecks()) {
            return $participants;
        }

        if ($event->bracket_type === 'single_elim') {
            $hasSingleElimRound = $event->rounds->where('stage', 'single_elim')->isNotEmpty();

            return $hasSingleElimRound ? collect() : $participants;
        }

        if (! $this->isSwissTopCutDeckRegistrationWindow($event)) {
            return collect();
        }

        $topCutPlayers = $this->topCutPlayers($event);
        $topCutByPlayerId = $participants->keyBy('player_id');

        return $topCutPlayers
            ->map(fn (Player $player) => $topCutByPlayerId->get($player->id))
            ->filter()
            ->values();
    }

    public function missingDeckRegistrationTargets(Event $event): Collection
    {
        return $this->deckRegistrationTargets($event)
            ->filter(fn (EventParticipant $participant) => ! $participant->hasRegisteredDeck())
            ->values();
    }

    private function generateSwissThenTopCutRound(Event $event, bool $reshuffle = false): string
    {
        $swissRounds = $event->rounds
            ->where('stage', 'swiss')
            ->sortBy('round_number')
            ->values();

        if ($reshuffle) {
            if ($swissRounds->isEmpty()) {
                $round = EventRound::query()->create([
                    'event_id' => $event->id,
                    'stage' => 'swiss',
                    'round_number' => 1,
                    'label' => 'Swiss Round 1',
                    'status' => 'pending',
                ]);

                $this->createPairingMatches($event, $round, $this->buildSwissPairings($event, 1, true));
                $this->refreshRoundStatus($round->load('matches'));
                $this->refreshEventStatus($event->fresh('rounds.matches'));

                return 'Swiss round 1 generated with reshuffled pairings.';
            }

            $roundOne = $swissRounds->first();

            if ((int) $roundOne->round_number !== 1 || ! $this->canReshuffleFirstSwissRound($event)) {
                throw new RuntimeException('Round 1 can only be reshuffled before any Swiss match results are recorded.');
            }

            $roundOne->matches()->delete();
            $this->createPairingMatches($event, $roundOne->fresh(), $this->buildSwissPairings($event, 1, true));
            $this->refreshRoundStatus($roundOne->fresh('matches'));
            $this->refreshEventStatus($event->fresh('rounds.matches'));

            return 'Swiss round 1 reshuffled.';
        }

        if ($swissRounds->count() < (int) $event->swiss_rounds) {
            if ($swissRounds->isNotEmpty() && $swissRounds->last()->status !== 'completed') {
                throw new RuntimeException('Finish the current Swiss round before generating the next one.');
            }

            $roundNumber = $swissRounds->count() + 1;
            $pairings = $this->buildSwissPairings($event, $roundNumber);
            $round = EventRound::query()->create([
                'event_id' => $event->id,
                'stage' => 'swiss',
                'round_number' => $roundNumber,
                'label' => "Swiss Round {$roundNumber}",
                'status' => 'pending',
            ]);

            $this->createPairingMatches($event, $round, $pairings);
            $this->refreshRoundStatus($round->load('matches'));
            $this->refreshEventStatus($event->fresh('rounds.matches'));

            return "Swiss round {$roundNumber} generated.";
        }

        if ($swissRounds->last()?->status !== 'completed') {
            throw new RuntimeException('Finish all Swiss rounds before generating the top cut.');
        }

        return $this->generateSingleEliminationRound($event, true);
    }

    private function generateSingleEliminationRound(Event $event, bool $fromSwissCut = false): string
    {
        $singleElimRounds = $event->rounds
            ->where('stage', 'single_elim')
            ->sortBy('round_number')
            ->values();

        if ($singleElimRounds->isEmpty()) {
            $players = $fromSwissCut
                ? $this->topCutPlayers($event)
                : $event->participants->sortBy('id')->values();

            if (! $event->usesLockedDecks()) {
                $missingDeckParticipants = $this->missingDeckParticipantsForPlayers($event, $players);

                if ($missingDeckParticipants->isNotEmpty()) {
                    throw new RuntimeException(
                        $fromSwissCut
                            ? 'Register decks for all top cut qualifiers before generating single elimination.'
                            : 'Register decks for all elimination entrants before generating round 1.'
                    );
                }
            }

            if ($players->count() < 2) {
                throw new RuntimeException('At least two participants are required to generate a single elimination round.');
            }

            $round = EventRound::query()->create([
                'event_id' => $event->id,
                'stage' => 'single_elim',
                'round_number' => 1,
                'label' => $fromSwissCut ? 'Top Cut Round 1' : 'Elimination Round 1',
                'status' => 'pending',
            ]);

            $this->createSeededSingleEliminationMatches($event, $round, $players);
            $this->refreshRoundStatus($round->load('matches'));
            $this->refreshEventStatus($event->fresh('rounds.matches'));

            return $fromSwissCut ? 'Top cut generated.' : 'Single elimination round 1 generated.';
        }

        $latestRound = $singleElimRounds->last()->load('matches.winner');
        if ($latestRound->status !== 'completed') {
            throw new RuntimeException('Finish the current elimination round before generating the next one.');
        }

        $completedMatches = $latestRound->matches
            ->sortBy('match_number')
            ->values();

        if ($completedMatches->count() === 1) {
            $this->refreshEventStatus($event->fresh('rounds.matches'));

            throw new RuntimeException('The elimination bracket is already complete.');
        }

        $round = EventRound::query()->create([
            'event_id' => $event->id,
            'stage' => 'single_elim',
            'round_number' => $latestRound->round_number + 1,
            'label' => 'Elimination Round '.($latestRound->round_number + 1),
            'status' => 'pending',
        ]);

        $this->createAdvancedSingleEliminationMatches($event, $round, $completedMatches);
        $this->refreshRoundStatus($round->load('matches'));
        $this->refreshEventStatus($event->fresh('rounds.matches'));

        return "Elimination round {$round->round_number} generated.";
    }

    private function buildSwissPairings(Event $event, int $roundNumber, bool $reshuffle = false): Collection
    {
        if ($roundNumber === 1) {
            $players = $event->participants
                ->sortBy('id')
                ->values();

            if ($reshuffle) {
                $players = $players->shuffle()->values();
            }

            return $this->pairOrderedPlayersForSwiss($players);
        }

        $playerRows = $this->swissStandings($event)->map(fn (array $row) => [
            'player' => $row['player'],
            'byes' => $row['byes'],
            'match_points' => (int) $row['match_points'],
        ])->values();

        $pairings = collect();
        [$playerRows, $byePairings] = $this->extractSwissByePairing($playerRows);
        $pairings = $pairings->merge($byePairings);
        $playedPairs = $this->playedSwissPairs($event);

        $groups = $playerRows
            ->groupBy(fn (array $row) => (string) $row['match_points'])
            ->sortKeysDesc(SORT_NUMERIC);

        $floatedRow = null;

        foreach ($groups as $groupRows) {
            $currentGroup = $groupRows->values();

            if ($floatedRow) {
                $currentGroup = collect([$floatedRow])->merge($currentGroup)->values();
                $floatedRow = null;
            }

            if ($currentGroup->count() % 2 === 1) {
                $floatedRow = $currentGroup->pop();
            }

            $pairings = $pairings->merge($this->pairSwissScoreGroup($currentGroup, $playedPairs));
        }

        if ($floatedRow) {
            throw new RuntimeException('Swiss pairing could not be completed cleanly for the current standings.');
        }

        return $pairings->values();
    }

    private function pairOrderedPlayersForSwiss(Collection $players): Collection
    {
        $playerRows = $players->map(fn (Player $player) => ['player' => $player, 'byes' => 0])->values();
        $pairings = collect();
        [$playerRows, $byePairings] = $this->extractSwissByePairing($playerRows);
        $pairings = $pairings->merge($byePairings);

        while ($playerRows->isNotEmpty()) {
            $first = $playerRows->shift();
            $opponent = $playerRows->shift();

            $pairings->push([
                'player1' => $first['player'],
                'player2' => $opponent['player'],
                'is_bye' => false,
                'source_match1_id' => null,
                'source_match2_id' => null,
            ]);
        }

        return $pairings->values();
    }

    private function extractSwissByePairing(Collection $playerRows): array
    {
        if ($playerRows->count() % 2 === 0) {
            return [$playerRows->values(), collect()];
        }

        $byeRow = $playerRows
            ->reverse()
            ->first(fn (array $row) => ($row['byes'] ?? 0) === 0)
            ?? $playerRows->last();

        $remainingRows = $playerRows
            ->reject(fn (array $row) => $row['player']->id === $byeRow['player']->id)
            ->values();

        $byePairings = collect([[
            'player1' => $byeRow['player'],
            'player2' => null,
            'is_bye' => true,
            'source_match1_id' => null,
            'source_match2_id' => null,
        ]]);

        return [$remainingRows, $byePairings];
    }

    private function pairSwissScoreGroup(Collection $groupRows, Collection $playedPairs): Collection
    {
        $pairings = collect();
        $availableRows = $groupRows->values();

        while ($availableRows->count() >= 2) {
            $first = $availableRows->shift();
            $opponentIndex = $availableRows->search(function (array $candidate) use ($first, $playedPairs) {
                $key = $this->pairKey($first['player']->id, $candidate['player']->id);

                return ! $playedPairs->contains($key);
            });

            if ($opponentIndex === false) {
                $opponentIndex = 0;
            }

            $opponent = $availableRows->pull($opponentIndex);

            $pairings->push([
                'player1' => $first['player'],
                'player2' => $opponent['player'],
                'is_bye' => false,
                'source_match1_id' => null,
                'source_match2_id' => null,
            ]);
        }

        return $pairings;
    }

    private function topCutPlayers(Event $event): Collection
    {
        $topCutSize = max(2, (int) $event->top_cut_size);
        $standings = $this->swissStandings($event);
        $players = $standings->take($topCutSize)->pluck('player')->values();

        if ($players->count() < 2) {
            throw new RuntimeException('Not enough Swiss players are available to seed a top cut.');
        }

        return $players;
    }

    private function missingDeckParticipantsForPlayers(Event $event, Collection $players): Collection
    {
        $event->loadMissing('eventParticipants.player.user');

        $requiredPlayerIds = $players->pluck('id')->all();

        return $event->eventParticipants
            ->whereIn('player_id', $requiredPlayerIds)
            ->filter(fn (EventParticipant $participant) => ! $participant->hasRegisteredDeck())
            ->values();
    }

    private function isSwissTopCutDeckRegistrationWindow(Event $event): bool
    {
        if (! $event->usesSwissBracket()) {
            return false;
        }

        $swissRounds = $event->rounds
            ->where('stage', 'swiss')
            ->sortBy('round_number')
            ->values();

        if ($swissRounds->count() < (int) $event->swiss_rounds) {
            return false;
        }

        if ($swissRounds->last()?->status !== 'completed') {
            return false;
        }

        return $event->rounds->where('stage', 'single_elim')->isEmpty();
    }

    private function createSeededSingleEliminationMatches(Event $event, EventRound $round, Collection $players): void
    {
        $pairings = collect();
        $seededPlayers = $players->values();

        if ($seededPlayers->count() % 2 === 1) {
            $pairings->push([
                'player1' => $seededPlayers->shift(),
                'player2' => null,
                'is_bye' => true,
                'source_match1_id' => null,
                'source_match2_id' => null,
            ]);
        }

        while ($seededPlayers->count() > 1) {
            $pairings->push([
                'player1' => $seededPlayers->shift(),
                'player2' => $seededPlayers->pop(),
                'is_bye' => false,
                'source_match1_id' => null,
                'source_match2_id' => null,
            ]);
        }

        if ($seededPlayers->isNotEmpty()) {
            $pairings->push([
                'player1' => $seededPlayers->shift(),
                'player2' => null,
                'is_bye' => true,
                'source_match1_id' => null,
                'source_match2_id' => null,
            ]);
        }

        $this->createPairingMatches($event, $round, $pairings->values());
    }

    private function createAdvancedSingleEliminationMatches(Event $event, EventRound $round, Collection $completedMatches): void
    {
        $pairings = collect();

        foreach ($completedMatches->chunk(2) as $chunk) {
            $first = $chunk->get(0);
            $second = $chunk->get(1);

            $pairings->push([
                'player1' => $first?->winner,
                'player2' => $second?->winner,
                'is_bye' => ! $second,
                'source_match1_id' => $first?->id,
                'source_match2_id' => $second?->id,
            ]);
        }

        $this->createPairingMatches($event, $round, $pairings->values());
    }

    private function createPairingMatches(Event $event, EventRound $round, Collection $pairings): void
    {
        $threshold = $event->battleWinThreshold();

        $pairings->values()->each(function (array $pairing, int $index) use ($event, $round, $threshold): void {
            $isBye = (bool) $pairing['is_bye'];

            EventMatch::query()->create([
                'event_id' => $event->id,
                'event_round_id' => $round->id,
                'stage' => $round->stage,
                'player1_id' => $pairing['player1']->id,
                'player2_id' => $pairing['player2']?->id,
                'player1_score' => $isBye ? $threshold : 0,
                'player2_score' => 0,
                'winner_id' => $isBye ? $pairing['player1']->id : null,
                'round_number' => $round->round_number,
                'match_number' => $index + 1,
                'status' => $isBye ? 'completed' : 'pending',
                'is_bye' => $isBye,
                'source_match1_id' => $pairing['source_match1_id'],
                'source_match2_id' => $pairing['source_match2_id'],
                'result_1' => null,
                'result_2' => null,
                'result_3' => null,
                'result_4' => null,
                'result_5' => null,
                'result_6' => null,
                'result_7' => null,
                'result_type_1' => null,
                'result_type_2' => null,
                'result_type_3' => null,
                'result_type_4' => null,
                'result_type_5' => null,
                'result_type_6' => null,
                'result_type_7' => null,
                'player1_bey1' => null,
                'player1_bey2' => null,
                'player1_bey3' => null,
                'player2_bey1' => null,
                'player2_bey2' => null,
                'player2_bey3' => null,
            ]);
        });
    }

    private function playedSwissPairs(Event $event): Collection
    {
        return $event->matches
            ->where('stage', 'swiss')
            ->reject(fn (EventMatch $match) => $match->is_bye || ! $match->player2_id)
            ->map(fn (EventMatch $match) => $this->pairKey($match->player1_id, $match->player2_id))
            ->values();
    }

    private function pairKey(int $player1Id, int $player2Id): string
    {
        return collect([$player1Id, $player2Id])->sort()->implode(':');
    }

    private function syncAutomaticResultsAndAwards(Event $event): void
    {
        DB::transaction(function () use ($event): void {
            $placements = $this->resolvedPlacements($event);

            EventResult::query()->where('event_id', $event->id)->delete();
            EventAward::query()->where('event_id', $event->id)->delete();

            foreach ($placements as $placement) {
                EventResult::query()->create([
                    'event_id' => $event->id,
                    'player_id' => $placement['player_id'],
                    'placement' => $placement['placement'],
                ]);
            }

            $championId = $placements->firstWhere('placement', 1)['player_id'] ?? null;
            if (! $championId) {
                return;
            }

            if ($event->usesSwissBracket()) {
                $swissLeaderId = $this->swissStandings($event)->first()['player']->id ?? null;

                $this->assignAutomaticAward($event, 'Swiss Champ', $championId);

                if ($swissLeaderId) {
                    $this->assignAutomaticAward($event, 'Swiss King', $swissLeaderId);
                }

                return;
            }

            $this->assignAutomaticAward($event, 'Bird King', $championId);
        });
    }

    private function clearAutomaticResultsAndAwards(Event $event): void
    {
        EventResult::query()->where('event_id', $event->id)->delete();
        EventAward::query()->where('event_id', $event->id)->delete();
    }

    private function assignAutomaticAward(Event $event, string $awardName, int $playerId): void
    {
        $awardId = Award::query()->where('name', $awardName)->value('id');

        if (! $awardId) {
            return;
        }

        EventAward::query()->create([
            'event_id' => $event->id,
            'player_id' => $playerId,
            'award_id' => $awardId,
        ]);
    }

    private function resolvedPlacements(Event $event): Collection
    {
        $singleElimRounds = $event->rounds
            ->where('stage', 'single_elim')
            ->sortBy('round_number')
            ->values();

        if ($singleElimRounds->isEmpty()) {
            return collect();
        }

        $finalRound = $singleElimRounds->last();
        $finalMatch = $finalRound->matches
            ->sortBy('match_number')
            ->first();

        if (! $finalMatch?->winner_id) {
            return collect();
        }

        $placements = collect([
            [
                'placement' => 1,
                'player_id' => $finalMatch->winner_id,
            ],
        ]);

        $runnerUpId = $this->matchLoserId($finalMatch);
        if ($runnerUpId) {
            $placements->push([
                'placement' => 2,
                'player_id' => $runnerUpId,
            ]);
        }

        if ($singleElimRounds->count() >= 2) {
            $semifinalRound = $singleElimRounds->get($singleElimRounds->count() - 2);

            $semifinalLosers = $semifinalRound->matches
                ->sortBy('match_number')
                ->map(fn (EventMatch $match) => $this->matchLoserId($match))
                ->filter()
                ->unique()
                ->values();

            if ($event->usesSwissBracket()) {
                $swissRanks = $this->swissStandings($event)
                    ->mapWithKeys(fn (array $row) => [$row['player']->id => $row['rank']]);

                $semifinalLosers = $semifinalLosers
                    ->sortBy(fn (int $playerId) => $swissRanks[$playerId] ?? PHP_INT_MAX)
                    ->values();
            }

            foreach ($semifinalLosers->take(2) as $index => $playerId) {
                if ($placements->contains(fn (array $placement) => $placement['player_id'] === $playerId)) {
                    continue;
                }

                $placements->push([
                    'placement' => 3 + $index,
                    'player_id' => $playerId,
                ]);
            }
        }

        if ($event->usesSwissBracket() && $placements->count() < 4) {
            $usedPlayerIds = $placements->pluck('player_id');

            $swissFallbacks = $this->swissStandings($event)
                ->pluck('player.id')
                ->reject(fn (int $playerId) => $usedPlayerIds->contains($playerId))
                ->values();

            foreach ($swissFallbacks as $playerId) {
                if ($placements->count() >= 4) {
                    break;
                }

                $placements->push([
                    'placement' => $placements->count() + 1,
                    'player_id' => $playerId,
                ]);
            }
        }

        return $placements
            ->unique(fn (array $placement) => $placement['player_id'])
            ->sortBy('placement')
            ->take(4)
            ->values();
    }

    private function matchLoserId(EventMatch $match): ?int
    {
        if (! $match->player2_id || ! $match->winner_id) {
            return null;
        }

        return $match->winner_id === $match->player1_id
            ? $match->player2_id
            : $match->player1_id;
    }
}
