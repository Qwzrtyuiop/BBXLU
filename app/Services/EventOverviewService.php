<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventAward;
use App\Models\EventMatch;
use App\Models\EventParticipant;
use App\Models\EventResult;
use App\Models\EventType;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EventOverviewService
{
    public function __construct(private readonly BracketService $bracketService)
    {
    }

    public function homeData(): array
    {
        $ongoingTournament = $this->ongoingTournament();
        $latestEvent = $this->latestEvent();

        return [
            'stats' => $this->stats(),
            'events' => $this->allEvents(),
            'ongoingTournament' => $ongoingTournament,
            'latestEvent' => $latestEvent,
            'latestEventPlacements' => $this->latestEventPlacements($latestEvent),
            'awardLeaders' => $this->awardLeaders(),
        ];
    }

    public function dashboardData(?int $selectedEventId = null, string $activePanel = 'overview'): array
    {
        $ongoingTournament = $this->activeDashboardEvent();
        $latestEvent = $this->latestEvent();
        $selectedEvent = $this->selectedEvent($selectedEventId, $activePanel, $ongoingTournament, $latestEvent);

        $data = [
            'stats' => $this->stats(includeUpcoming: true),
            'ongoingTournament' => $ongoingTournament,
            'latestEvent' => $latestEvent,
            'latestChampion' => $this->latestChampion($latestEvent),
            'latestEventPlacements' => collect(),
            'upcomingEvents' => collect(),
            'awardLeaders' => collect(),
            'adminEvents' => collect(),
            'adminEventPreviews' => collect(),
            'eventTypes' => collect(),
            'selectedEvent' => $selectedEvent,
            'selectedEventParticipants' => collect(),
            'selectedEventResults' => collect(),
            'selectedEventAwards' => collect(),
            'selectedEventRounds' => collect(),
            'selectedSwissStandings' => collect(),
            'selectedDeckRegistrationTargets' => collect(),
            'selectedMissingDeckRegistrations' => collect(),
        ];

        if ($activePanel === 'overview') {
            $data['latestEventPlacements'] = $this->latestEventPlacements($latestEvent);
            $data['upcomingEvents'] = $this->upcomingEvents(6);
            $data['awardLeaders'] = $this->awardLeaders();
        }

        if ($activePanel === 'events') {
            $data['adminEvents'] = $this->adminEvents();
            $data['adminEventPreviews'] = $this->adminEventPreviews($data['adminEvents']);
            $data['eventTypes'] = $this->eventTypes();
        }

        if ($activePanel === 'workspace') {
            $data['selectedEventParticipants'] = $this->selectedEventParticipants($selectedEvent);
            $data['selectedEventResults'] = $this->selectedEventResults($selectedEvent);
            $data['selectedEventAwards'] = $this->selectedEventAwards($selectedEvent);
            $data['selectedEventRounds'] = $this->selectedEventRounds($selectedEvent);
            $data['selectedSwissStandings'] = $this->selectedSwissStandings($selectedEvent);
            $data['selectedDeckRegistrationTargets'] = $this->selectedDeckRegistrationTargets($selectedEvent);
            $data['selectedMissingDeckRegistrations'] = $this->selectedMissingDeckRegistrations($selectedEvent);
        }

        return $data;
    }

    private function stats(bool $includeUpcoming = false): array
    {
        $stats = [
            'users' => User::count(),
            'players' => Player::count(),
            'events' => Event::count(),
        ];

        if ($includeUpcoming) {
            $stats['upcoming'] = Event::where('status', 'upcoming')->count();
        }

        return $stats;
    }

    private function allEvents(): Collection
    {
        return Event::query()
            ->with(['eventType', 'creator'])
            ->withCount('participants')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
    }

    private function ongoingTournament(): ?Event
    {
        return Event::query()
            ->with(['eventType', 'creator'])
            ->withCount('participants')
            ->where('is_active', true)
            ->where('status', 'upcoming')
            ->orderBy('date')
            ->orderBy('id')
            ->first();
    }

    private function activeDashboardEvent(): ?Event
    {
        return Event::query()
            ->with(['eventType', 'creator'])
            ->withCount('participants')
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN status = 'upcoming' THEN 0 ELSE 1 END")
            ->orderBy('date')
            ->orderByDesc('id')
            ->first();
    }

    private function latestEvent(): ?Event
    {
        return Event::query()
            ->with(['eventType', 'creator'])
            ->withCount('participants')
            ->where('status', 'finished')
            ->whereHas('results.player.user')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();
    }

    private function latestEventPlacements(?Event $event): Collection
    {
        if (! $event) {
            return collect();
        }

        return EventResult::query()
            ->with('player.user')
            ->where('event_id', $event->id)
            ->whereHas('player.user')
            ->orderBy('placement')
            ->limit(4)
            ->get();
    }

    private function latestChampion(?Event $event): ?EventResult
    {
        if (! $event) {
            return null;
        }

        return EventResult::query()
            ->with('player.user')
            ->where('event_id', $event->id)
            ->whereHas('player.user')
            ->orderBy('placement')
            ->first();
    }

    private function upcomingEvents(int $limit): Collection
    {
        return Event::query()
            ->with(['eventType', 'creator'])
            ->withCount('participants')
            ->where('status', 'upcoming')
            ->orderBy('date')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    private function adminEvents(): Collection
    {
        return Event::query()
            ->with(['eventType', 'creator'])
            ->withCount('participants')
            ->get()
            ->sort(function (Event $left, Event $right): int {
                $leftRank = $left->is_active ? 0 : ($left->status === 'upcoming' ? 1 : 2);
                $rightRank = $right->is_active ? 0 : ($right->status === 'upcoming' ? 1 : 2);

                if ($leftRank !== $rightRank) {
                    return $leftRank <=> $rightRank;
                }

                if ($leftRank === 2) {
                    return $right->date->timestamp <=> $left->date->timestamp
                        ?: $right->id <=> $left->id;
                }

                return $left->date->timestamp <=> $right->date->timestamp
                    ?: $left->id <=> $right->id;
            })
            ->values();
    }

    private function eventTypes(): Collection
    {
        return EventType::query()->orderBy('name')->get();
    }

    private function adminEventPreviews(Collection $events): Collection
    {
        return $events->mapWithKeys(function (Event $event): array {
            $participants = $this->selectedEventParticipants($event);
            $results = $this->selectedEventResults($event);
            $awards = $this->selectedEventAwards($event);
            $rounds = $this->selectedEventRounds($event);
            $swissStandings = $this->selectedSwissStandings($event);
            $deckTargets = $this->selectedDeckRegistrationTargets($event);
            $missingDeckRegistrations = $this->selectedMissingDeckRegistrations($event);
            $allMatches = $rounds
                ->flatMap(fn ($round) => $round->matches->sortBy('match_number')->values())
                ->values();

            return [
                $event->id => [
                    'participants' => $participants,
                    'results' => $results,
                    'awards' => $awards,
                    'rounds' => $rounds,
                    'swissStandings' => $swissStandings,
                    'pendingMatchCount' => $allMatches->where('status', 'pending')->count(),
                    'completedMatchCount' => $allMatches->where('status', 'completed')->count(),
                    'deckTargetCount' => $deckTargets->count(),
                    'missingDeckCount' => $missingDeckRegistrations->count(),
                ],
            ];
        });
    }

    private function selectedEvent(?int $selectedEventId, string $activePanel, ?Event $ongoingTournament, ?Event $latestEvent): ?Event
    {
        $query = Event::query()
            ->with(['eventType', 'creator'])
            ->withCount('participants');

        if ($activePanel === 'workspace') {
            return $ongoingTournament;
        }

        if ($activePanel === 'events') {
            return $selectedEventId ? $query->find($selectedEventId) : null;
        }

        if ($selectedEventId) {
            return $query->find($selectedEventId);
        }

        return $ongoingTournament
            ?? $latestEvent
            ?? $query->orderByDesc('date')->orderByDesc('id')->first();
    }

    private function selectedEventParticipants(?Event $event): Collection
    {
        if (! $event) {
            return collect();
        }

        return $event->eventParticipants()
            ->with('player.user')
            ->whereHas('player.user')
            ->get()
            ->sortBy(fn (EventParticipant $participant) => strtolower($participant->player->user->nickname))
            ->values();
    }

    private function selectedEventResults(?Event $event): Collection
    {
        if (! $event) {
            return collect();
        }

        return $event->results()
            ->with('player.user')
            ->whereHas('player.user')
            ->orderBy('placement')
            ->get();
    }

    private function selectedEventAwards(?Event $event): Collection
    {
        if (! $event) {
            return collect();
        }

        return $event->awards()
            ->with(['award', 'player.user'])
            ->whereHas('player.user')
            ->get()
            ->sortBy(fn (EventAward $eventAward) => strtolower($eventAward->award->name))
            ->values();
    }

    private function selectedEventRounds(?Event $event): Collection
    {
        if (! $event) {
            return collect();
        }

        return $event->rounds()
            ->with(['matches.player1.user', 'matches.player2.user', 'matches.winner.user'])
            ->orderByRaw("case when stage = 'swiss' then 0 when stage = 'single_elim' then 1 else 2 end")
            ->orderBy('round_number')
            ->get()
            ->map(function ($round) {
                $round->setRelation(
                    'matches',
                    $round->matches
                        ->filter(fn (EventMatch $match) => $this->matchHasRenderablePlayers($match))
                        ->values()
                );

                return $round;
            });
    }

    private function selectedSwissStandings(?Event $event): Collection
    {
        if (! $event || ! $event->usesSwissBracket()) {
            return collect();
        }

        return $this->bracketService->swissStandings($event);
    }

    private function selectedDeckRegistrationTargets(?Event $event): Collection
    {
        if (! $event) {
            return collect();
        }

        return $this->bracketService->deckRegistrationTargets($event);
    }

    private function selectedMissingDeckRegistrations(?Event $event): Collection
    {
        if (! $event) {
            return collect();
        }

        return $this->bracketService->missingDeckRegistrationTargets($event);
    }

    private function awardLeaders(): Collection
    {
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

        $leaders = DB::table('event_awards as ea')
            ->join('awards as a', 'a.id', '=', 'ea.award_id')
            ->join('players as p', 'p.id', '=', 'ea.player_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->whereIn('a.name', collect($awardCategories)->pluck('award_name'))
            ->selectRaw('a.name as award_name, u.nickname, count(*) as total')
            ->groupBy('a.name', 'u.nickname')
            ->orderBy('a.name')
            ->orderByDesc('total')
            ->orderBy('u.nickname')
            ->get()
            ->groupBy('award_name')
            ->map(fn (Collection $rows) => $rows->first());

        return collect($awardCategories)->map(function (array $category) use ($leaders) {
            $leader = $leaders->get($category['award_name']);

            return [
                'title' => $category['title'],
                'description' => $category['description'],
                'award_name' => $category['award_name'],
                'nickname' => $leader?->nickname,
                'total' => $leader?->total ?? 0,
            ];
        });
    }

    private function matchHasRenderablePlayers(EventMatch $match): bool
    {
        if (! $match->player1) {
            return false;
        }

        if (! $match->is_bye && $match->player2_id && ! $match->player2) {
            return false;
        }

        if ($match->winner_id && ! $match->winner) {
            return false;
        }

        return true;
    }
}
