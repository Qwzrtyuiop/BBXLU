<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventAward;
use App\Models\EventMatch;
use App\Models\EventParticipant;
use App\Models\EventRound;
use App\Models\EventResult;
use App\Models\EventType;
use App\Models\Player;
use App\Models\StadiumSide;
use App\Models\User;
use App\Services\BracketService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;

class EventController extends Controller
{
    public function index(): RedirectResponse
    {
        return $this->dashboardPanelRedirect('events');
    }

    public function create(): RedirectResponse
    {
        return $this->dashboardPanelRedirect('events');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateEventData($request);
        $creator = $this->resolveCreator($data['created_by_nickname']);
        $event = Event::query()->create($this->buildEventPayload($data, $creator->id));

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', 'Event created successfully.');
    }

    public function edit(Event $event): RedirectResponse
    {
        return $this->dashboardPanelRedirect('events', $event->id);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        if ($redirect = $this->ensureEventDetailsEditable($request, $event)) {
            return $redirect;
        }

        $data = $this->validateEventData($request);
        $creator = $this->resolveCreator($data['created_by_nickname']);
        $event->update($this->buildEventPayload($data, $creator->id, $event));

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', 'Event updated successfully.');
    }

    public function activate(Request $request, Event $event): RedirectResponse
    {
        if (! in_array($event->status, ['upcoming', 'finished'], true)) {
            return $this->redirectTarget($request, 'events.edit', [$event], $event)
                ->withErrors([
                    'active_event' => 'Only upcoming or finished events can be set as active.',
                ]);
        }

        $currentSessionActiveEventId = (int) $request->session()->get('dashboard_active_event_id', 0);

        if ($currentSessionActiveEventId === $event->id) {
            return $this->redirectTarget($request, 'events.edit', [$event], $event)
                ->with('status', 'This event is already active in your admin session.');
        }

        $request->session()->put('dashboard_active_event_id', $event->id);

        return $this->redirectTarget($request, 'events.edit', [$event], $event)
            ->with('status', "{$event->title} is now active in your admin session.");
    }

    public function toggleLive(Request $request, Event $event): RedirectResponse
    {
        if (! in_array($event->status, ['upcoming', 'finished'], true)) {
            return $this->redirectTarget($request, 'events.edit', [$event], $event)
                ->withErrors([
                    'live_event' => 'Only upcoming or finished events can be toggled live.',
                ]);
        }

        $event->forceFill([
            'is_active' => ! $event->is_active,
        ])->save();

        return $this->redirectTarget($request, 'events.edit', [$event], $event)
            ->with('status', $event->is_active
                ? "{$event->title} is now public live."
                : "{$event->title} is no longer public live.");
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $event->delete();

        return $this->redirectTarget($request, 'events.index')
            ->with('status', 'Event deleted.');
    }

    public function show(Event $event): RedirectResponse
    {
        return $this->dashboardPanelRedirect('workspace', $event->id);
    }

    public function generateBracketRound(Request $request, Event $event, BracketService $bracketService): RedirectResponse
    {
        try {
            $status = $bracketService->generateNextRound($event, $request->boolean('reshuffle'));
        } catch (RuntimeException $exception) {
            return $this->redirectTarget($request, 'events.show', [$event], $event)
                ->withErrors([
                    'bracket' => $exception->getMessage(),
                ]);
        }

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', $status);
    }

    public function regenerateAutomaticResultsAndAwards(Request $request, Event $event, BracketService $bracketService): RedirectResponse
    {
        try {
            $bracketService->regenerateAutomaticResultsAndAwards($event);
        } catch (RuntimeException $exception) {
            return $this->redirectTarget($request, 'events.show', [$event], $event)
                ->withErrors([
                    'awards' => $exception->getMessage(),
                ]);
        }

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', 'Automatic placements and awards regenerated.');
    }

    public function storeParticipant(Request $request, Event $event): RedirectResponse
    {
        if ($redirect = $this->ensureParticipantChangesAllowed($request, $event)) {
            return $redirect;
        }

        $data = $request->validate([
            'nickname' => ['nullable', 'string', 'max:255'],
            'selected_nicknames' => ['nullable', 'array'],
            'selected_nicknames.*' => ['nullable', 'string', 'max:255'],
            'deck_bey1' => [Rule::requiredIf(fn () => $event->usesLockedDecks()), 'nullable', 'string', 'max:255'],
            'deck_bey2' => [Rule::requiredIf(fn () => $event->usesLockedDecks()), 'nullable', 'string', 'max:255'],
            'deck_bey3' => [Rule::requiredIf(fn () => $event->usesLockedDecks()), 'nullable', 'string', 'max:255'],
        ]);

        $nicknames = $this->participantNicknames($data);

        if ($nicknames === []) {
            return back()->withErrors([
                'nickname' => 'Add at least one nickname before saving participants.',
            ])->withInput();
        }

        $hasDeckInput = $this->hasDeckRegistrationInput($data);

        if (count($nicknames) > 1 && $event->usesLockedDecks()) {
            return back()->withErrors([
                'selected_nicknames' => 'Locked-deck events must be registered one player at a time with a deck list.',
            ])->withInput();
        }

        if (count($nicknames) > 1 && $hasDeckInput) {
            return back()->withErrors([
                'deck_bey1' => 'Deck registration can only be saved when adding one player at a time.',
            ])->withInput();
        }

        $deckPayload = $this->participantDeckPayload($data);

        $result = DB::transaction(function () use ($event, $nicknames, $deckPayload, $hasDeckInput) {
            $summary = [
                'participants_added' => 0,
                'users_created' => 0,
                'participants_existing' => 0,
                'deck_registered' => false,
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

                if ($hasDeckInput) {
                    $this->applyParticipantDeckRegistration($participant, $deckPayload);
                    $summary['deck_registered'] = true;
                }
            }

            return $summary;
        });

        $status = $this->participantStatusMessage(
            $result['participants_added'],
            $result['users_created'],
            $result['participants_existing'],
            $result['deck_registered']
        );

        return $this->redirectTarget($request, 'events.show', [$event], $event)->with('status', $status);
    }

    public function updateParticipantDeck(Request $request, Event $event, Player $player): RedirectResponse
    {
        $participant = EventParticipant::query()
            ->where('event_id', $event->id)
            ->where('player_id', $player->id)
            ->first();

        if (! $participant) {
            abort(404);
        }

        $data = $request->validate([
            'deck_bey1' => ['required', 'string', 'max:255'],
            'deck_bey2' => ['required', 'string', 'max:255'],
            'deck_bey3' => ['required', 'string', 'max:255'],
        ]);

        $this->applyParticipantDeckRegistration($participant, $this->participantDeckPayload($data));

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', 'Deck registration saved.')
            ->with('deck_modal_reopen', true)
            ->with('deck_modal_focus_player_id', $participant->player_id);
    }

    public function bulkUpdateParticipantDecks(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'decks' => ['required', 'array', 'min:1'],
            'decks.*.deck_bey1' => ['required', 'string', 'max:255'],
            'decks.*.deck_bey2' => ['required', 'string', 'max:255'],
            'decks.*.deck_bey3' => ['required', 'string', 'max:255'],
        ]);

        $playerIds = collect(array_keys($data['decks']))
            ->map(fn ($playerId) => (int) $playerId)
            ->filter(fn (int $playerId) => $playerId > 0)
            ->values();

        $participants = EventParticipant::query()
            ->where('event_id', $event->id)
            ->whereIn('player_id', $playerIds)
            ->get()
            ->keyBy('player_id');

        if ($participants->count() !== $playerIds->count()) {
            abort(404);
        }

        DB::transaction(function () use ($data, $participants): void {
            foreach ($data['decks'] as $playerId => $deckPayload) {
                $participant = $participants->get((int) $playerId);

                if (! $participant) {
                    continue;
                }

                $this->applyParticipantDeckRegistration($participant, $this->participantDeckPayload($deckPayload));
            }
        });

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', 'Bulk deck registration saved.')
            ->with('deck_modal_reopen', true);
    }

    public function destroyParticipant(Request $request, Event $event, Player $player): RedirectResponse
    {
        if ($redirect = $this->ensureParticipantChangesAllowed($request, $event)) {
            return $redirect;
        }

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

    public function storeMatch(Request $request, Event $event, BracketService $bracketService): RedirectResponse
    {
        $validationRules = [
            'match_id' => ['nullable', 'exists:matches,id'],
            'event_round_id' => ['nullable', 'exists:event_rounds,id'],
            'stage' => ['nullable', 'in:swiss,single_elim'],
            'player1_id' => ['required', 'exists:players,id'],
            'player2_id' => ['nullable', 'exists:players,id'],
            'player1_score' => ['nullable', 'integer', 'min:0'],
            'player2_score' => ['nullable', 'integer', 'min:0'],
            'round_number' => ['nullable', 'integer', 'min:1'],
            'match_number' => ['nullable', 'integer', 'min:1'],
            'is_bye' => ['nullable', 'boolean'],
            'player1_bey1' => ['nullable', 'string', 'max:255'],
            'player1_bey2' => ['nullable', 'string', 'max:255'],
            'player1_bey3' => ['nullable', 'string', 'max:255'],
            'player2_bey1' => ['nullable', 'string', 'max:255'],
            'player2_bey2' => ['nullable', 'string', 'max:255'],
            'player2_bey3' => ['nullable', 'string', 'max:255'],
            'player1_stadium_side' => ['nullable', 'in:X,B,Other'],
            'player2_stadium_side' => ['nullable', 'in:X,B,Other'],
        ];

        foreach (range(1, EventMatch::MAX_BATTLE_SLOTS) as $slot) {
            $validationRules["result_{$slot}"] = ['nullable', 'integer', 'in:1,2'];
            $validationRules["result_type_{$slot}"] = [
                Rule::requiredIf(fn () => filled($request->input("result_{$slot}"))),
                'nullable',
                'in:spin,burst,over,extreme',
            ];
        }

        $data = $request->validate($validationRules);

        $match = ! empty($data['match_id'])
            ? EventMatch::query()->findOrFail($data['match_id'])
            : new EventMatch();

        if ($match->exists && $match->event_id !== $event->id) {
            abort(404);
        }

        $isBye = (bool) ($data['is_bye'] ?? false);

        if (! $isBye && empty($data['player2_id'])) {
            return back()->withErrors([
                'player2_id' => 'Player 2 is required unless this match is a bye.',
            ])->withInput();
        }

        if (! empty($data['player2_id']) && (int) $data['player1_id'] === (int) $data['player2_id']) {
            return back()->withErrors([
                'match_players' => 'player1_id and player2_id must be different.',
            ])->withInput();
        }

        if (! $this->isEventParticipant($event->id, (int) $data['player1_id'])
            || (! empty($data['player2_id']) && ! $this->isEventParticipant($event->id, (int) $data['player2_id']))) {
            return back()->withErrors([
                'match_players' => 'Both players must be participants in this event.',
            ])->withInput();
        }

        $eventRound = null;
        if (! empty($data['event_round_id'])) {
            $eventRound = EventRound::query()->findOrFail($data['event_round_id']);

            if ($eventRound->event_id !== $event->id) {
                abort(404);
            }
        }

        $stage = $eventRound?->stage
            ?? ($data['stage'] ?? $match->stage)
            ?? ($event->usesSwissBracket() && (int) ($data['round_number'] ?? $match->round_number ?? 1) <= (int) $event->swiss_rounds ? 'swiss' : 'single_elim');
        $threshold = $this->matchWinThreshold($event, $eventRound, $match, $stage);

        try {
            $summary = $this->matchSummaryFromRequest($event, $data, $isBye, $threshold);
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'match_scores' => $exception->getMessage(),
            ])->withInput();
        }

        try {
            $resolvedBeys = [
                'player1_bey1' => $this->resolvedMatchBeyValue($event, $stage, (int) $data['player1_id'], 1, $data['player1_bey1'] ?? null),
                'player1_bey2' => $this->resolvedMatchBeyValue($event, $stage, (int) $data['player1_id'], 2, $data['player1_bey2'] ?? null),
                'player1_bey3' => $this->resolvedMatchBeyValue($event, $stage, (int) $data['player1_id'], 3, $data['player1_bey3'] ?? null),
                'player2_bey1' => $summary['player2_id'] ? $this->resolvedMatchBeyValue($event, $stage, (int) $summary['player2_id'], 1, $data['player2_bey1'] ?? null) : null,
                'player2_bey2' => $summary['player2_id'] ? $this->resolvedMatchBeyValue($event, $stage, (int) $summary['player2_id'], 2, $data['player2_bey2'] ?? null) : null,
                'player2_bey3' => $summary['player2_id'] ? $this->resolvedMatchBeyValue($event, $stage, (int) $summary['player2_id'], 3, $data['player2_bey3'] ?? null) : null,
            ];
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'match_deck' => $exception->getMessage(),
            ])->withInput();
        }

        $matchPayload = [
            'event_id' => $event->id,
            'event_round_id' => $eventRound?->id ?? $match->event_round_id,
            'stage' => $stage,
            'player1_id' => $data['player1_id'],
            'player2_id' => $summary['player2_id'],
            'player1_score' => $summary['player1_score'],
            'player2_score' => $summary['player2_score'],
            'winner_id' => $summary['winner_id'],
            'round_number' => $eventRound?->round_number ?? $data['round_number'] ?? $match->round_number,
            'match_number' => $data['match_number'] ?? $match->match_number,
            'status' => $summary['status'],
            'is_bye' => $summary['is_bye'],
            'player1_bey1' => $resolvedBeys['player1_bey1'],
            'player1_bey2' => $resolvedBeys['player1_bey2'],
            'player1_bey3' => $resolvedBeys['player1_bey3'],
            'player2_bey1' => $resolvedBeys['player2_bey1'],
            'player2_bey2' => $resolvedBeys['player2_bey2'],
            'player2_bey3' => $resolvedBeys['player2_bey3'],
        ];

        $matchPayload = array_merge($matchPayload, $this->resolvedMatchStadiumSides($data, $summary['player2_id']));

        foreach (range(1, EventMatch::MAX_BATTLE_SLOTS) as $slot) {
            $resultIndex = $slot - 1;
            $matchPayload["result_{$slot}"] = $summary['results'][$resultIndex] ?? null;
            $matchPayload["result_type_{$slot}"] = ($summary['results'][$resultIndex] ?? null) !== null
                ? ($data["result_type_{$slot}"] ?? null)
                : null;
        }

        $match->fill($matchPayload);
        $match->save();

        $statusMessage = $match->wasRecentlyCreated ? 'Match recorded.' : 'Match updated.';

        if ($match->event_round_id) {
            $round = $match->round()->with('matches')->first();
            if ($round) {
                $bracketService->refreshRoundStatus($round);

                try {
                    $autoAdvanceStatus = $round->stage === 'single_elim'
                        ? $bracketService->syncSingleEliminationProgression($event)
                        : ($round->status === 'completed'
                            ? $bracketService->advanceBracketAfterRoundCompletion($round)
                            : null);
                } catch (RuntimeException $exception) {
                    $bracketService->refreshEventStatus($event->fresh('rounds.matches'));

                    return $this->redirectTarget($request, 'events.show', [$event], $event)
                        ->with('status', $statusMessage)
                        ->withErrors([
                            'bracket' => $exception->getMessage(),
                        ]);
                }

                if ($autoAdvanceStatus) {
                    $statusMessage .= ' '.$autoAdvanceStatus;
                }
            }
        }

        $bracketService->refreshEventStatus($event->fresh('rounds.matches'));

        return $this->redirectTarget($request, 'events.show', [$event], $event)->with('status', $statusMessage);
    }

    public function destroyMatch(Request $request, Event $event, EventMatch $match, BracketService $bracketService): RedirectResponse
    {
        if ($match->event_id !== $event->id) {
            abort(404);
        }

        $round = $match->round;
        $match->delete();

        if ($round) {
            if ($round->matches()->exists()) {
                $bracketService->refreshRoundStatus($round->fresh('matches'));
            } else {
                $round->delete();
            }
        }

        $bracketService->refreshEventStatus($event->fresh('rounds.matches'));

        return $this->redirectTarget($request, 'events.show', [$event], $event)->with('status', 'Match deleted.');
    }

    private function validateEventData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'bracket_type' => ['required', 'in:single_elim,swiss_single_elim'],
            'swiss_rounds' => [Rule::requiredIf(fn () => $request->input('bracket_type') === 'swiss_single_elim'), 'nullable', 'integer', 'min:1', 'max:12'],
            'top_cut_size' => [Rule::requiredIf(fn () => $request->input('bracket_type') === 'swiss_single_elim'), 'nullable', 'integer', 'in:2,4,8,16,32,64'],
            'is_lock_deck' => ['nullable', 'boolean'],
            'date' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:upcoming,finished'],
            'created_by_nickname' => ['required', 'string', 'max:255'],
        ]);
    }

    private function buildEventPayload(array $data, int $creatorId, ?Event $existingEvent = null): array
    {
        $usesSwiss = $data['bracket_type'] === 'swiss_single_elim';

        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'challonge_link' => null,
            'challonge_url' => null,
            'event_type_id' => $data['event_type_id'],
            'bracket_type' => $data['bracket_type'],
            'swiss_rounds' => $usesSwiss ? ($data['swiss_rounds'] ?: null) : null,
            'top_cut_size' => $usesSwiss ? ($data['top_cut_size'] ?: 8) : null,
            'match_format' => 7,
            'date' => $data['date'],
            'location' => $data['location'],
            'status' => $data['status'],
            'is_lock_deck' => (bool) ($data['is_lock_deck'] ?? false),
            'is_active' => $data['status'] === 'finished'
                ? false
                : ($existingEvent?->is_active ?? false),
            'created_by' => $creatorId,
        ];
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

    private function participantStatusMessage(int $participantsAdded, int $usersCreated, int $participantsExisting, bool $deckRegistered): string
    {
        if ($participantsAdded === 0) {
            $status = $participantsExisting === 1
                ? 'Participant already exists in this event.'
                : 'All selected participants already exist in this event.';

            return $deckRegistered ? $status.' Deck registration updated.' : $status;
        }

        if ($participantsAdded === 1 && $usersCreated === 1 && $participantsExisting === 0) {
            return $deckRegistered
                ? 'Participant added with auto-created account and locked deck.'
                : 'Participant added with auto-created account.';
        }

        if ($participantsAdded === 1 && $usersCreated === 0 && $participantsExisting === 0) {
            return $deckRegistered
                ? 'Participant added and deck registered.'
                : 'Participant added successfully.';
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

        if ($deckRegistered) {
            $status .= ' Deck registration saved.';
        }

        return $status;
    }

    private function hasDeckRegistrationInput(array $data): bool
    {
        return filled($data['deck_bey1'] ?? null)
            || filled($data['deck_bey2'] ?? null)
            || filled($data['deck_bey3'] ?? null);
    }

    private function participantDeckPayload(array $data): array
    {
        return [
            'deck_bey1' => trim((string) ($data['deck_bey1'] ?? '')),
            'deck_bey2' => trim((string) ($data['deck_bey2'] ?? '')),
            'deck_bey3' => trim((string) ($data['deck_bey3'] ?? '')),
        ];
    }

    private function applyParticipantDeckRegistration(EventParticipant $participant, array $deckPayload): void
    {
        $participant->forceFill([
            'deck_name' => null,
            'deck_bey1' => $deckPayload['deck_bey1'],
            'deck_bey2' => $deckPayload['deck_bey2'],
            'deck_bey3' => $deckPayload['deck_bey3'],
            'deck_registered_at' => now(),
        ])->save();
    }

    private function resolvedMatchBeyValue(Event $event, string $stage, int $playerId, int $slot, ?string $manualValue): ?string
    {
        $participant = EventParticipant::query()
            ->where('event_id', $event->id)
            ->where('player_id', $playerId)
            ->first();

        if (! $participant) {
            return $manualValue;
        }

        if ($event->usesLockedDecks() || $stage === 'single_elim') {
            $column = "deck_bey{$slot}";

            if (! filled($participant->{$column})) {
                throw new RuntimeException('Deck registration is required before this match can be saved.');
            }

            return $participant->{$column};
        }

        return $manualValue;
    }

    private function matchSummaryFromRequest(Event $event, array $data, bool $isBye, int $threshold): array
    {
        if ($isBye) {
            return [
                'player2_id' => null,
                'player1_score' => $threshold,
                'player2_score' => 0,
                'winner_id' => (int) $data['player1_id'],
                'status' => 'completed',
                'is_bye' => true,
                'results' => array_fill(0, EventMatch::MAX_BATTLE_SLOTS, null),
            ];
        }

        $results = collect(range(1, EventMatch::MAX_BATTLE_SLOTS))
            ->map(fn (int $index) => $data["result_{$index}"] ?? null)
            ->all();
        $resultTypes = collect(range(1, EventMatch::MAX_BATTLE_SLOTS))
            ->map(fn (int $index) => $data["result_type_{$index}"] ?? null)
            ->all();

        if (collect($results)->filter()->isEmpty()) {
            return $this->legacyMatchSummaryFromScores($data, $threshold);
        }

        $scoredResult = $this->scoredMatchResultSummary($results, $resultTypes, $threshold, $event->match_format);

        return [
            'player2_id' => (int) $data['player2_id'],
            'player1_score' => $scoredResult['player1_score'],
            'player2_score' => $scoredResult['player2_score'],
            'winner_id' => $scoredResult['player1_score'] > $scoredResult['player2_score']
                ? (int) $data['player1_id']
                : (int) $data['player2_id'],
            'status' => 'completed',
            'is_bye' => false,
            'results' => $scoredResult['results'],
        ];
    }

    private function resolvedMatchStadiumSides(array $data, ?int $player2Id): array
    {
        $player1Side = $this->normalizeStadiumSideCode($data['player1_stadium_side'] ?? null);
        $player2Side = $this->normalizeStadiumSideCode($data['player2_stadium_side'] ?? null);

        if ($player1Side === 'X' || $player1Side === 'B') {
            $player2Side = $player1Side === 'X' ? 'B' : 'X';
        } elseif ($player2Side === 'X' || $player2Side === 'B') {
            $player1Side = $player2Side === 'X' ? 'B' : 'X';
        }

        if (! $player2Id) {
            $player2Side = null;
        }

        $sideIds = StadiumSide::query()
            ->whereIn('code', array_filter([$player1Side, $player2Side]))
            ->pluck('id', 'code');

        return [
            'player1_stadium_side_id' => $player1Side ? $sideIds->get($player1Side) : null,
            'player2_stadium_side_id' => $player2Side ? $sideIds->get($player2Side) : null,
        ];
    }

    private function normalizeStadiumSideCode(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return in_array($normalized, ['X', 'B', 'Other'], true)
            ? $normalized
            : null;
    }

    private function scoredMatchResultSummary(array $results, array $resultTypes, int $threshold, int $matchFormat): array
    {
        $player1Score = 0;
        $player2Score = 0;
        $trimmedResults = array_fill(0, count($results), null);
        $encounteredBlank = false;
        $matchFinishedAt = null;

        foreach (array_keys($results) as $index) {
            $winner = $results[$index] ?? null;

            if ($winner === null) {
                $encounteredBlank = true;
                continue;
            }

            if ($encounteredBlank) {
                throw new RuntimeException('Battle results must be filled in order without gaps.');
            }

            if ($matchFinishedAt !== null) {
                throw new RuntimeException("Stop entering battles after a player reaches at least {$threshold} points.");
            }

            $trimmedResults[$index] = $winner;
            $points = EventMatch::finishTypePoints($resultTypes[$index] ?? null);

            if ((int) $winner === 1) {
                $player1Score += $points;
            } else {
                $player2Score += $points;
            }

            if (max($player1Score, $player2Score) >= $threshold) {
                $matchFinishedAt = $index;
            }
        }

        if ($player1Score === $player2Score) {
            throw new RuntimeException('Matches cannot end in a tie.');
        }

        if (max($player1Score, $player2Score) < $threshold) {
            throw new RuntimeException("A match result must reach at least {$threshold} points.");
        }

        return [
            'player1_score' => $player1Score,
            'player2_score' => $player2Score,
            'results' => $trimmedResults,
        ];
    }

    private function legacyMatchSummaryFromScores(array $data, int $threshold): array
    {
        $player1Score = (int) ($data['player1_score'] ?? 0);
        $player2Score = (int) ($data['player2_score'] ?? 0);

        if ($player1Score === $player2Score) {
            throw new RuntimeException('Matches cannot end in a tie.');
        }

        if (max($player1Score, $player2Score) < $threshold) {
            throw new RuntimeException("Winner score must reach at least {$threshold} points.");
        }

        return [
            'player2_id' => (int) $data['player2_id'],
            'player1_score' => $player1Score,
            'player2_score' => $player2Score,
            'winner_id' => $player1Score > $player2Score ? (int) $data['player1_id'] : (int) $data['player2_id'],
            'status' => 'completed',
            'is_bye' => false,
            'results' => $this->legacyMatchResultsFromScores($player1Score, $player2Score),
        ];
    }

    private function legacyMatchResultsFromScores(int $player1Score, int $player2Score): array
    {
        $winnerSlot = $player1Score > $player2Score ? 1 : 2;
        $winnerScore = max($player1Score, $player2Score);
        $loserScore = min($player1Score, $player2Score);
        $loserSlot = $winnerSlot === 1 ? 2 : 1;

        return array_pad(array_slice(array_merge(
            array_fill(0, $loserScore, $loserSlot),
            array_fill(0, $winnerScore, $winnerSlot),
        ), 0, EventMatch::MAX_BATTLE_SLOTS), EventMatch::MAX_BATTLE_SLOTS, null);
    }

    private function matchWinThreshold(Event $event, ?EventRound $eventRound, EventMatch $match, ?string $stage): int
    {
        if (! $eventRound && $match->event_round_id) {
            $eventRound = $match->round()->with('matches')->first();
        }

        $roundMatchCount = $eventRound
            ? ($eventRound->relationLoaded('matches') ? $eventRound->matches->count() : $eventRound->matches()->count())
            : null;

        return $event->battleWinThresholdForMatch($match, $eventRound, $stage, $roundMatchCount);
    }

    private function redirectTarget(
        Request $request,
        string $fallbackRoute,
        array $fallbackParameters = [],
        ?Event $event = null
    ): RedirectResponse {
        $eventId = $event?->id ?: $request->integer('dashboard_event_id');

        if ($request->boolean('dashboard_redirect')) {
            return $this->dashboardPanelRedirect(
                $this->normalizeDashboardPanel($request->string('dashboard_panel')->toString()),
                $eventId > 0 ? $eventId : null
            );
        }

        $dashboardPanel = match ($fallbackRoute) {
            'events.index', 'events.create' => 'events',
            'events.show' => 'workspace',
            'events.edit' => 'events',
            'players.index' => 'players',
            default => null,
        };

        if ($dashboardPanel !== null) {
            return $this->dashboardPanelRedirect(
                $dashboardPanel,
                $eventId > 0 ? $eventId : null
            );
        }

        return redirect()->route($fallbackRoute, $fallbackParameters);
    }

    private function dashboardPanelRedirect(string $panel, ?int $eventId = null): RedirectResponse
    {
        $parameters = ['panel' => $this->normalizeDashboardPanel($panel)];

        if ($eventId) {
            $parameters['event'] = $eventId;
        }

        return redirect()->route('dashboard', $parameters);
    }

    private function normalizeDashboardPanel(string $panel): string
    {
        return in_array($panel, ['overview', 'events', 'workspace', 'players'], true)
            ? $panel
            : 'overview';
    }

    private function ensureEventDetailsEditable(Request $request, Event $event): ?RedirectResponse
    {
        if (! $event->hasStarted()) {
            return null;
        }

        return $this->redirectTarget($request, 'events.edit', [$event], $event)
            ->withErrors([
                'event_locked' => 'Event details are locked once bracket play has started.',
            ]);
    }

    private function ensureParticipantChangesAllowed(Request $request, Event $event): ?RedirectResponse
    {
        if (! $event->hasStarted()) {
            return null;
        }

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->withErrors([
                'participants' => 'Participants are locked once bracket play has started.',
            ]);
    }
}
