<?php

namespace App\Services;

use App\Models\Award;
use App\Models\Event;
use App\Models\EventAward;
use App\Models\EventResult;
use App\Models\EventType;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EventOverviewService
{
    public function homeData(): array
    {
        $ongoingTournament = $this->ongoingTournament();
        $latestEvent = $this->latestEvent();

        return [
            'stats' => $this->stats(),
            'events' => $this->allEvents(),
            'ongoingTournament' => $ongoingTournament,
            'ongoingTournamentLink' => $ongoingTournament?->resolvedChallongeLink(),
            'latestEvent' => $latestEvent,
            'latestEventPlacements' => $this->latestEventPlacements($latestEvent),
            'awardLeaders' => $this->awardLeaders(),
        ];
    }

    public function dashboardData(?int $selectedEventId = null, string $activePanel = 'overview'): array
    {
        $ongoingTournament = $this->ongoingTournament();
        $latestEvent = $this->latestEvent();
        $selectedEvent = $this->selectedEvent($selectedEventId, $ongoingTournament, $latestEvent);

        $data = [
            'stats' => $this->stats(includeUpcoming: true),
            'ongoingTournament' => $ongoingTournament,
            'ongoingTournamentLink' => $ongoingTournament?->resolvedChallongeLink(),
            'latestEvent' => $latestEvent,
            'latestChampion' => $this->latestChampion($latestEvent),
            'latestEventPlacements' => collect(),
            'upcomingEvents' => collect(),
            'awardLeaders' => collect(),
            'adminEvents' => collect(),
            'eventTypes' => collect(),
            'awards' => collect(),
            'selectedEvent' => $selectedEvent,
            'selectedEventParticipants' => collect(),
            'selectedEventResults' => collect(),
            'selectedEventAwards' => collect(),
            'selectedEventMatches' => collect(),
        ];

        if ($activePanel === 'overview') {
            $data['latestEventPlacements'] = $this->latestEventPlacements($latestEvent);
            $data['upcomingEvents'] = $this->upcomingEvents(6);
            $data['awardLeaders'] = $this->awardLeaders();
        }

        if ($activePanel === 'events') {
            $data['adminEvents'] = $this->adminEvents();
            $data['eventTypes'] = $this->eventTypes();
        }

        if ($activePanel === 'workspace') {
            $data['awards'] = $this->awards();
            $data['selectedEventParticipants'] = $this->selectedEventParticipants($selectedEvent);
            $data['selectedEventResults'] = $this->selectedEventResults($selectedEvent);
            $data['selectedEventAwards'] = $this->selectedEventAwards($selectedEvent);
            $data['selectedEventMatches'] = $this->selectedEventMatches($selectedEvent);
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
            ->where('status', 'upcoming')
            ->orderBy('date')
            ->orderBy('id')
            ->first();
    }

    private function latestEvent(): ?Event
    {
        return Event::query()
            ->with(['eventType', 'creator'])
            ->withCount('participants')
            ->where('status', 'finished')
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
            ->orderBy('placement')
            ->first();
    }

    private function upcomingEvents(int $limit): Collection
    {
        return Event::query()
            ->with(['eventType', 'creator'])
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
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
    }

    private function eventTypes(): Collection
    {
        return EventType::query()->orderBy('name')->get();
    }

    private function awards(): Collection
    {
        return Award::query()->orderBy('name')->get();
    }

    private function selectedEvent(?int $selectedEventId, ?Event $ongoingTournament, ?Event $latestEvent): ?Event
    {
        $query = Event::query()
            ->with(['eventType', 'creator'])
            ->withCount('participants');

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

        return $event->participants()
            ->with('user')
            ->get()
            ->sortBy(fn (Player $player) => strtolower($player->user->nickname))
            ->values();
    }

    private function selectedEventResults(?Event $event): Collection
    {
        if (! $event) {
            return collect();
        }

        return $event->results()
            ->with('player.user')
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
            ->get()
            ->sortBy(fn (EventAward $eventAward) => strtolower($eventAward->award->name))
            ->values();
    }

    private function selectedEventMatches(?Event $event): Collection
    {
        if (! $event) {
            return collect();
        }

        return $event->matches()
            ->with(['player1.user', 'player2.user', 'winner.user'])
            ->orderByDesc('created_at')
            ->get();
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
}
