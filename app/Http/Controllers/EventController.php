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
        $data = $this->validateEventData($request);
        $creator = $this->resolveCreator($data['created_by_nickname']);
        $event->update($this->buildEventPayload($data, $creator->id, $event));

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', 'Event updated successfully.');
    }

    public function activate(Request $request, Event $event): RedirectResponse
    {
        if ($event->status !== 'upcoming') {
            return $this->redirectTarget($request, 'events.edit', [$event], $event)
                ->withErrors([
                    'active_event' => 'Only upcoming events can be set as active.',
                ]);
        }

        if ($event->is_active) {
            return $this->redirectTarget($request, 'events.edit', [$event], $event)
                ->with('status', 'This event is already active.');
        }

        DB::transaction(function () use ($event): void {
            Event::query()
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $event->forceFill([
                'is_active' => true,
            ])->save();
        });

        return $this->redirectTarget($request, 'events.edit', [$event], $event)
            ->with('status', "{$event->title} is now the active event.");
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

    public function storeParticipant(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'nickname' => ['nullable', 'string', 'max:255'],
            'selected_nicknames' => ['nullable', 'array'],
            'selected_nicknames.*' => ['nullable', 'string', 'max:255'],
            'deck_name' => [Rule::requiredIf(fn () => $event->usesLockedDecks()), 'nullable', 'string', 'max:255'],
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
                'deck_name' => 'Deck registration can only be saved when adding one player at a time.',
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
            'deck_name' => ['required', 'string', 'max:255'],
            'deck_bey1' => ['required', 'string', 'max:255'],
            'deck_bey2' => ['required', 'string', 'max:255'],
            'deck_bey3' => ['required', 'string', 'max:255'],
        ]);

        $this->applyParticipantDeckRegistration($participant, $this->participantDeckPayload($data));

        return $this->redirectTarget($request, 'events.show', [$event], $event)
            ->with('status', 'Deck registration saved.');
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

    public function storeMatch(Request $request, Event $event, BracketService $bracketService): RedirectResponse
    {
        $data = $request->validate([
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
            'result_1' => ['nullable', 'integer', 'in:1,2'],
            'result_2' => ['nullable', 'integer', 'in:1,2'],
            'result_3' => ['nullable', 'integer', 'in:1,2'],
            'result_4' => ['nullable', 'integer', 'in:1,2'],
            'result_5' => ['nullable', 'integer', 'in:1,2'],
            'result_6' => ['nullable', 'integer', 'in:1,2'],
            'result_7' => ['nullable', 'integer', 'in:1,2'],
            'result_type_1' => [Rule::requiredIf(fn () => filled($request->input('result_1'))), 'nullable', 'in:spin,burst,over,extreme'],
            'result_type_2' => [Rule::requiredIf(fn () => filled($request->input('result_2'))), 'nullable', 'in:spin,burst,over,extreme'],
            'result_type_3' => [Rule::requiredIf(fn () => filled($request->input('result_3'))), 'nullable', 'in:spin,burst,over,extreme'],
            'result_type_4' => [Rule::requiredIf(fn () => filled($request->input('result_4'))), 'nullable', 'in:spin,burst,over,extreme'],
            'result_type_5' => [Rule::requiredIf(fn () => filled($request->input('result_5'))), 'nullable', 'in:spin,burst,over,extreme'],
            'result_type_6' => [Rule::requiredIf(fn () => filled($request->input('result_6'))), 'nullable', 'in:spin,burst,over,extreme'],
            'result_type_7' => [Rule::requiredIf(fn () => filled($request->input('result_7'))), 'nullable', 'in:spin,burst,over,extreme'],
        ]);

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

        try {
            $summary = $this->matchSummaryFromRequest($event, $data, $isBye);
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'match_scores' => $exception->getMessage(),
            ])->withInput();
        }

        $stage = $eventRound?->stage
            ?? ($data['stage'] ?? $match->stage)
            ?? ($event->usesSwissBracket() && (int) ($data['round_number'] ?? $match->round_number ?? 1) <= (int) $event->swiss_rounds ? 'swiss' : 'single_elim');

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

        $match->fill([
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
            'result_1' => $summary['results'][0],
            'result_2' => $summary['results'][1],
            'result_3' => $summary['results'][2],
            'result_4' => $summary['results'][3],
            'result_5' => $summary['results'][4],
            'result_6' => $summary['results'][5],
            'result_7' => $summary['results'][6],
            'result_type_1' => $summary['results'][0] !== null ? ($data['result_type_1'] ?? null) : null,
            'result_type_2' => $summary['results'][1] !== null ? ($data['result_type_2'] ?? null) : null,
            'result_type_3' => $summary['results'][2] !== null ? ($data['result_type_3'] ?? null) : null,
            'result_type_4' => $summary['results'][3] !== null ? ($data['result_type_4'] ?? null) : null,
            'result_type_5' => $summary['results'][4] !== null ? ($data['result_type_5'] ?? null) : null,
            'result_type_6' => $summary['results'][5] !== null ? ($data['result_type_6'] ?? null) : null,
            'result_type_7' => $summary['results'][6] !== null ? ($data['result_type_7'] ?? null) : null,
        ]);
        $match->save();

        $statusMessage = $match->wasRecentlyCreated ? 'Match recorded.' : 'Match updated.';

        if ($match->event_round_id) {
            $round = $match->round()->with('matches')->first();
            if ($round) {
                $bracketService->refreshRoundStatus($round);

                if ($round->status === 'completed') {
                    try {
                        $autoAdvanceStatus = $bracketService->advanceSwissAfterRoundCompletion($round);
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
        return filled($data['deck_name'] ?? null)
            || filled($data['deck_bey1'] ?? null)
            || filled($data['deck_bey2'] ?? null)
            || filled($data['deck_bey3'] ?? null);
    }

    private function participantDeckPayload(array $data): array
    {
        return [
            'deck_name' => trim((string) ($data['deck_name'] ?? '')),
            'deck_bey1' => trim((string) ($data['deck_bey1'] ?? '')),
            'deck_bey2' => trim((string) ($data['deck_bey2'] ?? '')),
            'deck_bey3' => trim((string) ($data['deck_bey3'] ?? '')),
        ];
    }

    private function applyParticipantDeckRegistration(EventParticipant $participant, array $deckPayload): void
    {
        $participant->forceFill([
            'deck_name' => $deckPayload['deck_name'],
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

    private function matchSummaryFromRequest(Event $event, array $data, bool $isBye): array
    {
        $threshold = $event->battleWinThreshold();

        if ($isBye) {
            return [
                'player2_id' => null,
                'player1_score' => $threshold,
                'player2_score' => 0,
                'winner_id' => (int) $data['player1_id'],
                'status' => 'completed',
                'is_bye' => true,
                'results' => array_fill(0, 7, null),
            ];
        }

        $results = collect(range(1, 7))
            ->map(fn (int $index) => $data["result_{$index}"] ?? null)
            ->all();

        if (collect($results)->filter()->isEmpty()) {
            $results = $this->legacyMatchResultsFromScores($data, $threshold);
        }

        $player1Score = collect($results)->filter(fn ($value) => $value === 1)->count();
        $player2Score = collect($results)->filter(fn ($value) => $value === 2)->count();

        if ($player1Score === $player2Score) {
            throw new RuntimeException('Matches cannot end in a tie.');
        }

        if (max($player1Score, $player2Score) < $threshold) {
            throw new RuntimeException("A match result must reach {$threshold} wins in a best-of-{$event->match_format} set.");
        }

        return [
            'player2_id' => (int) $data['player2_id'],
            'player1_score' => $player1Score,
            'player2_score' => $player2Score,
            'winner_id' => $player1Score > $player2Score ? (int) $data['player1_id'] : (int) $data['player2_id'],
            'status' => 'completed',
            'is_bye' => false,
            'results' => array_pad(array_slice($results, 0, 7), 7, null),
        ];
    }

    private function legacyMatchResultsFromScores(array $data, int $threshold): array
    {
        $player1Score = (int) ($data['player1_score'] ?? 0);
        $player2Score = (int) ($data['player2_score'] ?? 0);

        if ($player1Score === $player2Score) {
            throw new RuntimeException('Matches cannot end in a tie.');
        }

        if (max($player1Score, $player2Score) < $threshold) {
            throw new RuntimeException("Winner score must reach {$threshold} for a best-of-7 match.");
        }

        return array_pad(array_merge(
            array_fill(0, $player1Score, 1),
            array_fill(0, $player2Score, 2),
        ), 7, null);
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
}
