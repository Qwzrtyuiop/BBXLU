<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\Event;
use App\Models\EventAward;
use App\Models\EventMatch;
use App\Models\EventParticipant;
use App\Models\EventResult;
use App\Models\EventType;
use App\Models\Player;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->with(['eventType', 'creator'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return view('events.index', compact('events'));
    }

    public function create(): View
    {
        $eventTypes = EventType::query()->orderBy('name')->get();

        return view('events.create', compact('eventTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateEventData($request);
        $creator = $this->resolveCreator($data['created_by_nickname']);
        $event = Event::query()->create($this->buildEventPayload($data, $creator->id));

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', 'Event created successfully.');
    }

    public function edit(Event $event): View
    {
        $eventTypes = EventType::query()->orderBy('name')->get();

        return view('events.edit', compact('event', 'eventTypes'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $this->validateEventData($request);
        $creator = $this->resolveCreator($data['created_by_nickname']);
        $event->update($this->buildEventPayload($data, $creator->id));

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', 'Event updated successfully.');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $event->delete();

        return $this->redirectTarget($request, 'events.index')
            ->with('status', 'Event deleted.');
    }

    public function show(Event $event): View
    {
        $event->load(['eventType', 'creator']);

        $participants = $event->participants()
            ->with('user')
            ->get()
            ->sortBy(fn (Player $player) => strtolower($player->user->nickname))
            ->values();

        $results = $event->results()
            ->with('player.user')
            ->orderBy('placement')
            ->get();

        $eventAwards = $event->awards()
            ->with(['award', 'player.user'])
            ->get()
            ->sortBy(fn (EventAward $eventAward) => strtolower($eventAward->award->name))
            ->values();

        $matches = $event->matches()
            ->with(['player1.user', 'player2.user', 'winner.user'])
            ->orderByDesc('created_at')
            ->get();

        $awards = Award::query()->orderBy('name')->get();

        return view('events.show', compact('event', 'participants', 'results', 'eventAwards', 'matches', 'awards'));
    }

    public function storeParticipant(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'nickname' => ['nullable', 'string', 'max:255'],
            'selected_nicknames' => ['nullable', 'array'],
            'selected_nicknames.*' => ['nullable', 'string', 'max:255'],
        ]);

        $nicknames = $this->participantNicknames($data);

        if ($nicknames === []) {
            return back()->withErrors([
                'nickname' => 'Add at least one nickname before saving participants.',
            ])->withInput();
        }

        $result = DB::transaction(function () use ($event, $nicknames) {
            $summary = [
                'participants_added' => 0,
                'users_created' => 0,
                'participants_existing' => 0,
            ];

            foreach ($nicknames as $nickname) {
                $user = User::query()->firstOrCreate(
                    ['nickname' => $nickname],
                    $this->autoCreatedUserAttributes($nickname)
                );

                if ($user->wasRecentlyCreated) {
                    $summary['users_created']++;
                }

                $player = Player::query()->firstOrCreate([
                    'user_id' => $user->id,
                ]);

                $participant = EventParticipant::query()->firstOrCreate([
                    'event_id' => $event->id,
                    'player_id' => $player->id,
                ]);

                if ($participant->wasRecentlyCreated) {
                    $summary['participants_added']++;
                } else {
                    $summary['participants_existing']++;
                }
            }

            return $summary;
        });

        $status = $this->participantStatusMessage(
            $result['participants_added'],
            $result['users_created'],
            $result['participants_existing']
        );

        return $this->redirectTarget($request, 'events.show', [$event], $event)->with('status', $status);
    }

    public function destroyParticipant(Request $request, Event $event, Player $player): RedirectResponse
    {
        if (! $this->isEventParticipant($event->id, $player->id)) {
            return $this->redirectTarget($request, 'events.show', [$event], $event)
                ->with('status', 'Participant is not in this event.');
        }

        DB::transaction(function () use ($event, $player): void {
            EventMatch::query()
                ->where('event_id', $event->id)
                ->where(function ($query) use ($player): void {
                    $query->where('player1_id', $player->id)
                        ->orWhere('player2_id', $player->id)
                        ->orWhere('winner_id', $player->id);
                })
                ->delete();

            EventResult::query()
                ->where('event_id', $event->id)
                ->where('player_id', $player->id)
                ->delete();

            EventAward::query()
                ->where('event_id', $event->id)
                ->where('player_id', $player->id)
                ->delete();

            EventParticipant::query()
                ->where('event_id', $event->id)
                ->where('player_id', $player->id)
                ->delete();
        });

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', 'Participant removed from event.');
    }

    public function storeResult(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'exists:players,id'],
            'placement' => ['required', 'integer', 'between:1,4'],
        ]);

        if (! $this->isEventParticipant($event->id, (int) $data['player_id'])) {
            return back()->withErrors([
                'result' => 'Player must be an event participant before adding results.',
            ])->withInput();
        }

        try {
            $result = EventResult::query()->firstOrNew([
                'event_id' => $event->id,
                'player_id' => $data['player_id'],
            ]);
            $result->placement = $data['placement'];
            $result->save();
        } catch (QueryException) {
            return back()->withErrors([
                'placement' => 'That placement is already taken for this event.',
            ])->withInput();
        }

        return $this->redirectTarget($request, 'events.show', [$event], $event)->with('status', 'Result saved.');
    }

    public function destroyResult(Request $request, Event $event, EventResult $result): RedirectResponse
    {
        if ($result->event_id !== $event->id) {
            abort(404);
        }

        $result->delete();

        return $this->redirectTarget($request, 'events.show', [$event], $event)->with('status', 'Result deleted.');
    }

    public function storeAward(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'exists:players,id'],
            'award_id' => ['required', 'exists:awards,id'],
        ]);

        if (! $this->isEventParticipant($event->id, (int) $data['player_id'])) {
            return back()->withErrors([
                'award' => 'Player must be an event participant before assigning awards.',
            ])->withInput();
        }

        EventAward::query()->updateOrCreate(
            [
                'event_id' => $event->id,
                'award_id' => $data['award_id'],
            ],
            [
                'player_id' => $data['player_id'],
            ]
        );

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', 'Award assignment saved.');
    }

    public function destroyAward(Request $request, Event $event, EventAward $eventAward): RedirectResponse
    {
        if ($eventAward->event_id !== $event->id) {
            abort(404);
        }

        $eventAward->delete();

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', 'Award assignment deleted.');
    }

    public function storeMatch(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'player1_id' => ['required', 'exists:players,id'],
            'player2_id' => ['required', 'exists:players,id'],
            'player1_score' => ['required', 'integer', 'min:0'],
            'player2_score' => ['required', 'integer', 'min:0'],
            'round_number' => ['nullable', 'integer', 'min:1'],
        ]);

        if ((int) $data['player1_id'] === (int) $data['player2_id']) {
            return back()->withErrors([
                'match_players' => 'player1_id and player2_id must be different.',
            ])->withInput();
        }

        if (! $this->isEventParticipant($event->id, (int) $data['player1_id'])
            || ! $this->isEventParticipant($event->id, (int) $data['player2_id'])) {
            return back()->withErrors([
                'match_players' => 'Both players must be participants in this event.',
            ])->withInput();
        }

        if ((int) $data['player1_score'] === (int) $data['player2_score']) {
            return back()->withErrors([
                'match_scores' => 'A tie cannot determine winner_id. Enter non-equal scores.',
            ])->withInput();
        }

        $winnerId = ((int) $data['player1_score'] > (int) $data['player2_score'])
            ? (int) $data['player1_id']
            : (int) $data['player2_id'];

        EventMatch::query()->create([
            'event_id' => $event->id,
            'player1_id' => $data['player1_id'],
            'player2_id' => $data['player2_id'],
            'player1_score' => $data['player1_score'],
            'player2_score' => $data['player2_score'],
            'winner_id' => $winnerId,
            'round_number' => $data['round_number'],
        ]);

        return $this->redirectTarget($request, 'events.show', [$event], $event)->with('status', 'Match recorded.');
    }

    public function destroyMatch(Request $request, Event $event, EventMatch $match): RedirectResponse
    {
        if ($match->event_id !== $event->id) {
            abort(404);
        }

        $match->delete();

        return $this->redirectTarget($request, 'events.show', [$event], $event)->with('status', 'Match deleted.');
    }

    private function validateEventData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'challonge_link' => ['nullable', 'url', 'max:2048'],
            'challonge_url' => ['nullable', 'url', 'max:2048'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'date' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:upcoming,finished'],
            'created_by_nickname' => ['required', 'string', 'max:255'],
        ]);
    }

    private function buildEventPayload(array $data, int $creatorId): array
    {
        $challongeLink = $this->normalizedChallongeLink($data);

        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'challonge_link' => $challongeLink,
            'challonge_url' => $challongeLink,
            'event_type_id' => $data['event_type_id'],
            'date' => $data['date'],
            'location' => $data['location'],
            'status' => $data['status'],
            'created_by' => $creatorId,
        ];
    }

    private function normalizedChallongeLink(array $data): ?string
    {
        foreach (['challonge_link', 'challonge_url'] as $field) {
            $value = trim((string) ($data[$field] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function isEventParticipant(int $eventId, int $playerId): bool
    {
        return EventParticipant::query()
            ->where('event_id', $eventId)
            ->where('player_id', $playerId)
            ->exists();
    }

    private function resolveCreator(string $nickname): User
    {
        $nickname = trim($nickname);

        return User::query()->firstOrCreate(
            ['nickname' => $nickname],
            $this->autoCreatedUserAttributes($nickname)
        );
    }

    private function autoCreatedUserAttributes(string $nickname): array
    {
        return [
            'name' => $nickname,
            'email' => null,
            'password' => null,
            'role' => 'user',
            'is_claimed' => false,
        ];
    }

    private function participantNicknames(array $data): array
    {
        return collect(array_merge(
            [$data['nickname'] ?? null],
            $data['selected_nicknames'] ?? [],
        ))
            ->map(fn ($nickname) => trim((string) $nickname))
            ->filter()
            ->unique(fn (string $nickname) => Str::lower($nickname))
            ->values()
            ->all();
    }

    private function participantStatusMessage(int $participantsAdded, int $usersCreated, int $participantsExisting): string
    {
        if ($participantsAdded === 0) {
            return $participantsExisting === 1
                ? 'Participant already exists in this event.'
                : 'All selected participants already exist in this event.';
        }

        if ($participantsAdded === 1 && $usersCreated === 1 && $participantsExisting === 0) {
            return 'Participant added with auto-created account.';
        }

        if ($participantsAdded === 1 && $usersCreated === 0 && $participantsExisting === 0) {
            return 'Participant added successfully.';
        }

        $status = $participantsAdded === 1
            ? '1 participant added.'
            : "{$participantsAdded} participants added.";

        if ($usersCreated > 0) {
            $status .= $usersCreated === 1
                ? ' 1 user profile auto-created.'
                : " {$usersCreated} user profiles auto-created.";
        }

        if ($participantsExisting > 0) {
            $status .= $participantsExisting === 1
                ? ' 1 participant was already in this event.'
                : " {$participantsExisting} participants were already in this event.";
        }

        return $status;
    }

    private function redirectTarget(
        Request $request,
        string $fallbackRoute,
        array $fallbackParameters = [],
        ?Event $event = null
    ): RedirectResponse {
        if ($request->boolean('dashboard_redirect')) {
            $panel = $request->string('dashboard_panel')->toString();
            $panel = in_array($panel, ['overview', 'events', 'workspace', 'players'], true)
                ? $panel
                : 'overview';

            $parameters = ['panel' => $panel];
            $eventId = $event?->id ?: $request->integer('dashboard_event_id');

            if ($eventId > 0) {
                $parameters['event'] = $eventId;
            }

            return redirect()->route('dashboard', $parameters);
        }

        return redirect()->route($fallbackRoute, $fallbackParameters);
    }
}
