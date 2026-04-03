<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventAward;
use App\Models\EventMatch;
use App\Models\EventParticipant;
use App\Models\EventResult;
use App\Models\Player;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class UserDashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role === 'admin') {
            return redirect()->route('dashboard');
        }

        $user->load('player.user');

        return view('user-dashboard', $this->profileViewData(
            viewer: $user,
            profileUser: $user,
            profilePlayer: $user->player,
            isSelfView: true
        ));
    }

    public function show(Request $request, Player $player): View|RedirectResponse
    {
        $viewer = $request->user();
        $player->load('user');

        if ($viewer?->player?->id === $player->id) {
            return redirect()->route('user.dashboard');
        }

        return view('player-profile', $this->profileViewData(
            viewer: $viewer,
            profileUser: $player->user,
            profilePlayer: $player,
            isSelfView: false
        ));
    }

    private function profileViewData(?User $viewer, ?User $profileUser, ?Player $profilePlayer, bool $isSelfView): array
    {
        $stats = $this->defaultStats();
        $upcomingEvents = collect();
        $recentResults = collect();
        $recentAwards = collect();
        $recentMatches = collect();
        $joinedEvents = collect();

        if ($profilePlayer) {
            $joinedEvents = $this->joinedEvents($profilePlayer);
            $upcomingEvents = $joinedEvents
                ->filter(fn (EventParticipant $participant) => $participant->event && $participant->event->status === 'upcoming')
                ->sortBy(fn (EventParticipant $participant) => $participant->event->date->timestamp)
                ->values()
                ->take(8);
            $recentResults = $this->recentResults($profilePlayer);
            $recentAwards = $this->recentAwards($profilePlayer);
            $recentMatches = $this->recentMatches($profilePlayer);
            $stats = $this->profileStats($profilePlayer, $joinedEvents, $recentMatches);
        }

        return [
            'viewer' => $viewer,
            'profileUser' => $profileUser,
            'profilePlayer' => $profilePlayer,
            'isSelfView' => $isSelfView,
            'profileStats' => $stats,
            'upcomingEvents' => $upcomingEvents,
            'recentResults' => $recentResults,
            'recentAwards' => $recentAwards,
            'recentMatches' => $recentMatches,
            'joinedEvents' => $joinedEvents,
            'publicProfileUrl' => $profilePlayer ? route('user.dashboard.profile', $profilePlayer) : null,
            'selfDashboardUrl' => route('user.dashboard'),
        ];
    }

    private function defaultStats(): array
    {
        return [
            'joined' => 0,
            'wins' => 0,
            'podiums' => 0,
            'top4' => 0,
            'awards' => 0,
            'match_wins' => 0,
            'match_losses' => 0,
            'match_record' => '0-0',
            'win_rate' => null,
            'top_cut_rate' => null,
            'top_cut_appearances' => 0,
            'swiss_events' => 0,
            'battle_points' => 0,
            'avg_score' => null,
            'avg_against' => null,
            'byes' => 0,
            'x_side_win_rate' => null,
            'b_side_win_rate' => null,
            'x_side_record' => '0-0',
            'b_side_record' => '0-0',
            'most_used_bey' => null,
            'most_used_bey_count' => 0,
            'best_finish' => null,
            'finish_percentages' => [
                'spin' => null,
                'burst' => null,
                'over' => null,
                'extreme' => null,
            ],
        ];
    }

    private function joinedEvents(Player $player): Collection
    {
        return EventParticipant::query()
            ->select('event_participants.*')
            ->join('events', 'events.id', '=', 'event_participants.event_id')
            ->with(['event.eventType', 'event.results'])
            ->where('event_participants.player_id', $player->id)
            ->orderByDesc('events.date')
            ->orderByDesc('events.id')
            ->get();
    }

    private function recentResults(Player $player): Collection
    {
        return EventResult::query()
            ->select('event_results.*')
            ->join('events', 'events.id', '=', 'event_results.event_id')
            ->with(['event.eventType'])
            ->where('event_results.player_id', $player->id)
            ->orderByDesc('events.date')
            ->orderByDesc('events.id')
            ->limit(8)
            ->get();
    }

    private function recentAwards(Player $player): Collection
    {
        return EventAward::query()
            ->select('event_awards.*')
            ->join('events', 'events.id', '=', 'event_awards.event_id')
            ->with(['award', 'event.eventType'])
            ->where('event_awards.player_id', $player->id)
            ->orderByDesc('events.date')
            ->orderByDesc('events.id')
            ->limit(8)
            ->get();
    }

    private function recentMatches(Player $player): Collection
    {
        return EventMatch::query()
            ->select('matches.*')
            ->join('events', 'events.id', '=', 'matches.event_id')
            ->with([
                'event.eventType',
                'round.matches',
                'player1.user',
                'player2.user',
                'winner.user',
                'player1StadiumSide',
                'player2StadiumSide',
            ])
            ->where(function ($query) use ($player): void {
                $query->where('matches.player1_id', $player->id)
                    ->orWhere('matches.player2_id', $player->id);
            })
            ->orderByDesc('events.date')
            ->orderByDesc('events.id')
            ->orderByDesc('matches.id')
            ->get();
    }

    private function profileStats(Player $player, Collection $joinedEvents, Collection $recentMatches): array
    {
        $stats = $this->defaultStats();
        $allResults = EventResult::query()->where('player_id', $player->id)->get();
        $completedMatches = $recentMatches->where('status', 'completed')->values();
        $completedCompetitiveMatches = $completedMatches
            ->filter(fn (EventMatch $match) => ! $match->is_bye && $match->player2_id)
            ->values();
        $matchWins = $completedCompetitiveMatches->where('winner_id', $player->id)->count();
        $matchLosses = $completedCompetitiveMatches
            ->filter(fn (EventMatch $match) => $match->winner_id && $match->winner_id !== $player->id)
            ->count();
        $swissEventIds = $joinedEvents
            ->filter(fn (EventParticipant $participant) => $participant->event?->usesSwissBracket())
            ->pluck('event_id')
            ->unique()
            ->values();
        $topCutAppearances = $recentMatches
            ->filter(fn (EventMatch $match) => $match->stage === 'single_elim' && $match->event?->usesSwissBracket())
            ->pluck('event_id')
            ->unique()
            ->count();
        $beyUsage = $joinedEvents
            ->flatMap(function (EventParticipant $participant) {
                return collect([
                    $participant->deck_bey1,
                    $participant->deck_bey2,
                    $participant->deck_bey3,
                ])->filter(fn (?string $bey) => filled($bey));
            })
            ->map(fn (string $bey) => trim($bey))
            ->filter()
            ->countBy();
        $mostUsedBey = $beyUsage->sortDesc()->keys()->first();
        $finishTypeCounts = collect([
            'spin' => 0,
            'burst' => 0,
            'over' => 0,
            'extreme' => 0,
        ]);
        $sideRecords = [
            'X' => ['wins' => 0, 'losses' => 0],
            'B' => ['wins' => 0, 'losses' => 0],
        ];

        foreach ($completedCompetitiveMatches as $match) {
            $playerSlot = $match->player1_id === $player->id
                ? 1
                : ($match->player2_id === $player->id ? 2 : null);

            if ($playerSlot === null) {
                continue;
            }

            foreach ($match->battleResults() as $battle) {
                if ((int) $battle['winner'] !== $playerSlot) {
                    continue;
                }

                $type = (string) ($battle['type'] ?? 'spin');
                if (! $finishTypeCounts->has($type)) {
                    continue;
                }

                $finishTypeCounts->put($type, ((int) $finishTypeCounts->get($type, 0)) + 1);
            }

            $sideCode = $match->stadiumSideCodeForPlayer($player->id);

            if (! in_array($sideCode, ['X', 'B'], true)) {
                continue;
            }

            if ($match->winner_id === $player->id) {
                $sideRecords[$sideCode]['wins']++;
            } elseif ($match->winner_id) {
                $sideRecords[$sideCode]['losses']++;
            }
        }

        $totalFinishWins = $finishTypeCounts->sum();
        $averageScore = $completedCompetitiveMatches->avg(function (EventMatch $match) use ($player): int {
            return $match->player1_id === $player->id
                ? (int) $match->player1_score
                : (int) $match->player2_score;
        });
        $averageAgainst = $completedCompetitiveMatches->avg(function (EventMatch $match) use ($player): int {
            return $match->player1_id === $player->id
                ? (int) $match->player2_score
                : (int) $match->player1_score;
        });

        $stats['joined'] = $joinedEvents->count();
        $stats['wins'] = $allResults->where('placement', 1)->count();
        $stats['podiums'] = $allResults->whereBetween('placement', [1, 3])->count();
        $stats['top4'] = $allResults->whereBetween('placement', [1, 4])->count();
        $stats['awards'] = EventAward::query()->where('player_id', $player->id)->count();
        $stats['match_wins'] = $matchWins;
        $stats['match_losses'] = $matchLosses;
        $stats['match_record'] = $matchWins.'-'.$matchLosses;
        $stats['win_rate'] = $completedCompetitiveMatches->isNotEmpty()
            ? round(($matchWins / $completedCompetitiveMatches->count()) * 100, 1)
            : null;
        $stats['top_cut_rate'] = $swissEventIds->isNotEmpty()
            ? round(($topCutAppearances / $swissEventIds->count()) * 100, 1)
            : null;
        $stats['top_cut_appearances'] = $topCutAppearances;
        $stats['swiss_events'] = $swissEventIds->count();
        $stats['battle_points'] = $completedMatches->sum(fn (EventMatch $match) => $match->weightedBattlePointsForPlayer($player->id));
        $stats['avg_score'] = $averageScore !== null ? round($averageScore, 1) : null;
        $stats['avg_against'] = $averageAgainst !== null ? round($averageAgainst, 1) : null;
        $stats['byes'] = $recentMatches->filter(fn (EventMatch $match) => $match->is_bye && $match->winner_id === $player->id)->count();
        $stats['x_side_record'] = $sideRecords['X']['wins'].'-'.$sideRecords['X']['losses'];
        $stats['b_side_record'] = $sideRecords['B']['wins'].'-'.$sideRecords['B']['losses'];
        $stats['x_side_win_rate'] = array_sum($sideRecords['X']) > 0
            ? round(($sideRecords['X']['wins'] / array_sum($sideRecords['X'])) * 100, 1)
            : null;
        $stats['b_side_win_rate'] = array_sum($sideRecords['B']) > 0
            ? round(($sideRecords['B']['wins'] / array_sum($sideRecords['B'])) * 100, 1)
            : null;
        $stats['most_used_bey'] = $mostUsedBey;
        $stats['most_used_bey_count'] = $mostUsedBey ? (int) $beyUsage->get($mostUsedBey) : 0;
        $stats['best_finish'] = $finishTypeCounts->sortDesc()->keys()->first(fn (string $type) => $finishTypeCounts->get($type) > 0);
        $stats['finish_percentages'] = $finishTypeCounts
            ->map(fn (int $count) => $totalFinishWins > 0 ? round(($count / $totalFinishWins) * 100, 1) : null)
            ->all();

        return $stats;
    }
}
