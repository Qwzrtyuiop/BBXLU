@php
    $layout = $layout ?? 'default';
    $matchIndex = $matchIndex ?? 1;
    $matchLabel = 'Match '.($match->match_number ?: $matchIndex);
    $roundLabel = $round->label ?: ucfirst(str_replace('_', ' ', $round->stage)).' Round '.$round->round_number;
    $matchTemplateId = 'workspace-match-template-'.$match->id;
    $isCompleted = $match->status === 'completed';
    $player1Name = $match->player1->user->nickname;
    $player2Name = $match->player2?->user->nickname ?? 'BYE';
    $player1Won = $match->winner_id === $match->player1_id;
    $player2Won = $match->winner_id === $match->player2_id;
    $player1Metric = $match->is_bye
        ? ($player1Won ? 'BYE' : '-')
        : ($isCompleted ? (string) $match->player1_score : '-');
    $player2Metric = $match->is_bye
        ? ($player2Won ? 'BYE' : '-')
        : ($isCompleted ? (string) $match->player2_score : '-');
    $cardBorderClasses = $match->is_bye
        ? 'border-emerald-500/35 bg-emerald-500/[0.05]'
        : ($isCompleted ? 'border-cyan-500/35 bg-cyan-500/[0.04]' : 'border-slate-800/90 bg-slate-950/80');
    $stateTextClasses = $match->is_bye
        ? 'text-emerald-300'
        : ($isCompleted ? 'text-cyan-200' : 'text-amber-200');
    $stateLabel = $match->is_bye
        ? 'Bye'
        : ($isCompleted ? $match->player1_score.'-'.$match->player2_score : 'Open');
    $footerLabel = $match->is_bye
        ? 'auto advance'
        : ($isCompleted ? 'winner: '.($match->winner?->user->nickname ?? 'TBD') : 'record result');
    $gridColumn = $gridColumn ?? null;
    $gridRowStart = $gridRowStart ?? null;
    $bracketConnectorHeightRem = $bracketConnectorHeightRem ?? 0;
    $showBracketConnector = $showBracketConnector ?? false;
    $showBracketOutgoingConnector = $showBracketOutgoingConnector ?? false;
    $bracketOutgoingDirection = $bracketOutgoingDirection ?? 'down';
    $bracketOutgoingHalfHeightRem = $bracketOutgoingHalfHeightRem ?? 0;
    $bracketLaneWidthRem = 1.75;
    $bracketColumnGapRem = 2;
@endphp

@if ($layout === 'swiss')
    <div>
        <button
            type="button"
            data-workspace-match-open
            data-match-template-id="{{ $matchTemplateId }}"
            data-match-modal-title="{{ $matchLabel }}"
            data-match-modal-subtitle="{{ $roundLabel }}"
            class="block w-full border px-2 py-1.5 text-left transition hover:border-cyan-400/55 hover:bg-slate-900/90 {{ $cardBorderClasses }}"
        >
            <div class="grid gap-1">
                <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 rounded border px-2 py-1 text-[10px] leading-tight {{ $player1Won ? 'border-amber-400/40 bg-amber-500/14 font-semibold text-slate-50' : 'border-slate-800/90 bg-slate-900/70 text-slate-200' }}">
                    <span class="truncate">{{ $player1Name }}</span>
                    <span class="text-right text-[9px] font-semibold {{ $player1Won ? 'text-amber-100' : 'text-slate-400' }}">{{ $player1Metric }}</span>
                </div>
                <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 rounded border px-2 py-1 text-[10px] leading-tight {{ $player2Won ? 'border-amber-400/40 bg-amber-500/14 font-semibold text-slate-50' : 'border-slate-800/90 bg-slate-900/70 text-slate-300' }}">
                    <span class="truncate">{{ $player2Name }}</span>
                    <span class="text-right text-[9px] font-semibold {{ $player2Won ? 'text-amber-100' : 'text-slate-400' }}">{{ $player2Metric }}</span>
                </div>
            </div>
        </button>
    </div>
@elseif ($layout === 'bracket')
    <div
        class="relative flex h-full min-h-[4.75rem] items-center overflow-visible"
        @if ($gridColumn !== null && $gridRowStart !== null)
            style="grid-column: {{ $gridColumn }}; grid-row: {{ $gridRowStart }};"
        @endif
    >
        @if ($showBracketConnector)
            <span
                class="absolute top-1/2 h-px -translate-y-1/2 bg-white"
                style="left: 0; width: {{ $bracketLaneWidthRem }}rem;"
            ></span>
            <span
                class="absolute w-px bg-white"
                style="left: 0; top: calc(50% - {{ $bracketConnectorHeightRem / 2 }}rem); height: {{ $bracketConnectorHeightRem }}rem;"
            ></span>
        @endif

        <button
            type="button"
            data-workspace-match-open
            data-match-template-id="{{ $matchTemplateId }}"
            data-match-modal-title="{{ $matchLabel }}"
            data-match-modal-subtitle="{{ $roundLabel }}"
            class="relative block w-full max-w-[11.5rem] border px-2 py-1.5 text-left transition hover:border-cyan-400/55 hover:bg-slate-900/90 {{ $cardBorderClasses }}"
            style="margin-left: {{ $bracketLaneWidthRem }}rem;"
        >
            @if ($showBracketOutgoingConnector)
                <span
                    class="absolute top-1/2 h-px -translate-y-1/2 bg-white"
                    style="left: calc(100% + 1px); width: {{ $bracketColumnGapRem }}rem;"
                ></span>
                <span
                    class="absolute w-px bg-white"
                    style="
                        left: calc(100% + {{ $bracketColumnGapRem }}rem + 1px);
                        top: {{ $bracketOutgoingDirection === 'down' ? '50%' : 'calc(50% - '.$bracketOutgoingHalfHeightRem.'rem)' }};
                        height: {{ $bracketOutgoingHalfHeightRem }}rem;
                    "
                ></span>
            @endif

            <div class="grid gap-1">
                <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 rounded border px-2 py-1 text-[10px] leading-tight {{ $player1Won ? 'border-amber-400/40 bg-amber-500/14 font-semibold text-slate-50' : 'border-slate-800/90 bg-slate-900/70 text-slate-200' }}">
                    <span class="truncate">{{ $player1Name }}</span>
                    <span class="text-right text-[9px] font-semibold {{ $player1Won ? 'text-amber-100' : 'text-slate-400' }}">{{ $player1Metric }}</span>
                </div>
                <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 rounded border px-2 py-1 text-[10px] leading-tight {{ $player2Won ? 'border-amber-400/40 bg-amber-500/14 font-semibold text-slate-50' : 'border-slate-800/90 bg-slate-900/70 text-slate-300' }}">
                    <span class="truncate">{{ $player2Name }}</span>
                    <span class="text-right text-[9px] font-semibold {{ $player2Won ? 'text-amber-100' : 'text-slate-400' }}">{{ $player2Metric }}</span>
                </div>
            </div>
        </button>
    </div>
@else
    <div>
        <button
            type="button"
            data-workspace-match-open
            data-match-template-id="{{ $matchTemplateId }}"
            data-match-modal-title="{{ $matchLabel }}"
            data-match-modal-subtitle="{{ $roundLabel }}"
            class="flex w-full flex-col border px-2.5 py-2 text-left transition hover:border-cyan-400/55 hover:bg-slate-900/90 {{ $cardBorderClasses }}"
        >
            <div class="flex items-center justify-between gap-2">
                <span class="text-[9px] uppercase tracking-[0.14em] text-slate-500">{{ $matchLabel }}</span>
                <span class="text-[9px] uppercase tracking-[0.14em] {{ $stateTextClasses }}">{{ $stateLabel }}</span>
            </div>

            <div class="mt-2 grid gap-1">
                <div class="flex items-center justify-between gap-2">
                    <span class="truncate text-[12px] {{ $player1Won ? 'font-semibold text-slate-50' : 'text-slate-200' }}">{{ $player1Name }}</span>
                    @if ($isCompleted && ! $match->is_bye)
                        <span class="text-[11px] text-slate-400">{{ $match->player1_score }}</span>
                    @endif
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="truncate text-[12px] {{ $player2Won ? 'font-semibold text-slate-50' : 'text-slate-400' }}">{{ $player2Name }}</span>
                    @if ($isCompleted && ! $match->is_bye)
                        <span class="text-[11px] text-slate-400">{{ $match->player2_score }}</span>
                    @endif
                </div>
            </div>

            <p class="mt-2 text-[10px] text-slate-500">
                @if ($match->is_bye)
                    {{ $match->winner?->user->nickname ?: $player1Name }} advances automatically.
                @elseif ($isCompleted)
                    Winner: {{ $match->winner?->user->nickname ?? 'TBD' }}
                @else
                    Click to record result.
                @endif
            </p>
        </button>
    </div>
@endif

    <template id="{{ $matchTemplateId }}" data-match-modal-title="{{ $matchLabel }}" data-match-modal-subtitle="{{ $roundLabel }}">
        @php
            $player1Participant = $participantDecks->get($match->player1_id);
            $player2Participant = $match->player2_id ? $participantDecks->get($match->player2_id) : null;
            $usesRegisteredDecks = $selectedEvent->usesLockedDecks() || $match->stage === 'single_elim';
            $matchWinThreshold = $selectedEvent->battleWinThresholdForStage($round->stage, $round->matches->count());
            $battleSlotCount = $selectedEvent->maxBattleSlotsForThreshold($matchWinThreshold);
            $isReopenedMatch = (int) old('match_id', 0) === $match->id;
            $formValue = fn (string $key, mixed $default = null) => $isReopenedMatch ? old($key, $default) : $default;
            $matchErrorMessages = $isReopenedMatch
                ? collect(array_merge(
                    ['match_scores', 'match_deck', 'match_players', 'player2_id'],
                    collect(range(1, $battleSlotCount))->flatMap(fn (int $slot) => [
                        "result_{$slot}",
                        "result_type_{$slot}",
                    ])->all(),
                    ['player1_bey1', 'player1_bey2', 'player1_bey3', 'player2_bey1', 'player2_bey2', 'player2_bey3']
                ))
                    ->map(fn (string $key) => $errors->first($key))
                    ->filter()
                    ->unique()
                    ->values()
                : collect();
        @endphp
        <article class="space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-medium text-slate-100">
                        {{ $player1Name }}
                        @if ($match->player2)
                            vs {{ $player2Name }}
                        @else
                            vs BYE
                        @endif
                    </p>
                    <p class="mt-1 text-[10px] uppercase tracking-[0.14em] text-slate-500">
                        {{ $match->status }}
                        @if ($match->is_bye)
                            - auto bye
                        @endif
                    </p>
                </div>
                <form action="{{ route('events.matches.destroy', [$selectedEvent, $match]) }}" method="POST" onsubmit="return confirm('Delete this match?');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="dashboard_redirect" value="1">
                    <input type="hidden" name="dashboard_panel" value="workspace">
                    <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                    <button class="border border-rose-500/60 px-2 py-1 text-[9px] font-semibold uppercase tracking-[0.14em] text-rose-200 transition hover:bg-rose-500/10">Delete</button>
                </form>
            </div>

            @if ($match->is_bye)
                <p class="text-sm text-emerald-200">{{ $match->winner?->user->nickname ?: $player1Name }} advances with a bye.</p>
            @elseif ($match->status === 'completed')
                <div class="grid gap-3 xl:grid-cols-2">
                    <div class="border border-slate-800 bg-slate-900/60 px-3 py-2">
                        <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Result</p>
                        <p class="mt-1 text-sm text-slate-100">{{ $player1Name }} {{ $match->player1_score }} - {{ $match->player2_score }} {{ $player2Name }}</p>
                        @if ($match->winner)
                            <p class="mt-1 text-[11px] text-emerald-300">Winner: {{ $match->winner->user->nickname }}</p>
                        @endif
                    </div>
                    <div class="border border-slate-800 bg-slate-900/60 px-3 py-2">
                        <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Battle History</p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($match->battleResults() as $battleResult)
                                <span class="rounded px-2 py-1 text-[10px] font-semibold {{ $battleResult['winner'] === 1 ? 'bg-cyan-500/20 text-cyan-200' : 'bg-rose-500/20 text-rose-200' }}">
                                    B{{ $battleResult['slot'] }}: {{ $battleResult['winner'] === 1 ? 'P1' : 'P2' }} @if ($battleResult['type'])- {{ ucfirst($battleResult['type']) }}@endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 xl:grid-cols-2">
                    @foreach ([
                        ['label' => $player1Name, 'values' => [$match->player1_bey1, $match->player1_bey2, $match->player1_bey3]],
                        ['label' => $player2Name, 'values' => [$match->player2_bey1, $match->player2_bey2, $match->player2_bey3]],
                    ] as $deckInfo)
                        <div class="border border-slate-800 bg-slate-900/60 px-3 py-2">
                            <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">{{ $deckInfo['label'] }} Bey Picks</p>
                            <p class="mt-1 text-sm text-slate-300">{{ collect($deckInfo['values'])->filter()->implode(', ') ?: 'Not recorded' }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <form action="{{ route('events.matches.store', $selectedEvent) }}" method="POST" class="grid gap-4">
                    @csrf
                    <input type="hidden" name="dashboard_redirect" value="1">
                    <input type="hidden" name="dashboard_panel" value="workspace">
                    <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                    <input type="hidden" name="match_id" value="{{ $match->id }}">
                    <input type="hidden" name="event_round_id" value="{{ $round->id }}">
                    <input type="hidden" name="stage" value="{{ $round->stage }}">
                    <input type="hidden" name="player1_id" value="{{ $match->player1_id }}">
                    <input type="hidden" name="player2_id" value="{{ $match->player2_id }}">
                    <input type="hidden" name="round_number" value="{{ $round->round_number }}">
                    <input type="hidden" name="match_number" value="{{ $match->match_number }}">

                    @if ($matchErrorMessages->isNotEmpty())
                        <div class="border border-rose-500/45 bg-rose-500/10 px-3 py-2">
                            @foreach ($matchErrorMessages as $message)
                                <p class="text-[11px] text-rose-200">{{ $message }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if ($usesRegisteredDecks)
                        <div class="grid gap-3 xl:grid-cols-2">
                            @foreach ([
                                ['label' => $player1Name, 'participant' => $player1Participant],
                                ['label' => $player2Name, 'participant' => $player2Participant],
                            ] as $deckInfo)
                                <div class="border border-slate-800 bg-slate-900/60 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">{{ $deckInfo['label'] }} Registered Deck</p>
                                    <p class="mt-1 text-sm text-slate-300">{{ $deckInfo['participant'] ? implode(', ', $deckInfo['participant']->registeredBeys()) : 'No deck registered yet.' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="grid gap-3 xl:grid-cols-2">
                            @foreach ([1 => $player1Name, 2 => $player2Name] as $slot => $label)
                                <div class="grid gap-2">
                                    <p class="text-sm font-semibold text-slate-100">{{ $label }} Bey Picks</p>
                                    @foreach ([1, 2, 3] as $beySlot)
                                        <label class="grid gap-1">
                                            <span class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Bey {{ $beySlot }}</span>
                                            <input
                                                name="player{{ $slot }}_bey{{ $beySlot }}"
                                                value="{{ $formValue('player'.$slot.'_bey'.$beySlot, $match->{'player'.$slot.'_bey'.$beySlot}) }}"
                                                class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                                            >
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <p class="text-sm font-semibold text-slate-100">Battle Results</p>
                        <p class="mt-1 text-xs text-slate-500">First to {{ $matchWinThreshold }} points. Spin = 1, Burst = 2, Over = 2, Extreme = 3.</p>
                        <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                            @foreach (range(1, $battleSlotCount) as $slot)
                                <div class="grid gap-2 border border-slate-800 bg-slate-900/45 p-3">
                                    <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Battle {{ $slot }}</p>
                                    <label class="grid gap-1">
                                        <span class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Winner</span>
                                        <select name="result_{{ $slot }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                            <option value="">-</option>
                                            <option value="1" @selected((string) $formValue('result_'.$slot, $match->{'result_'.$slot}) === '1')>{{ $player1Name }}</option>
                                            <option value="2" @selected((string) $formValue('result_'.$slot, $match->{'result_'.$slot}) === '2')>{{ $player2Name }}</option>
                                        </select>
                                    </label>
                                    <label class="grid gap-1">
                                        <span class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Finish</span>
                                        <select name="result_type_{{ $slot }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                            <option value="">-</option>
                                            <option value="spin" @selected($formValue('result_type_'.$slot, $match->{'result_type_'.$slot}) === 'spin')>Spin (1 pt)</option>
                                            <option value="burst" @selected($formValue('result_type_'.$slot, $match->{'result_type_'.$slot}) === 'burst')>Burst (2 pts)</option>
                                            <option value="over" @selected($formValue('result_type_'.$slot, $match->{'result_type_'.$slot}) === 'over')>Over (2 pts)</option>
                                            <option value="extreme" @selected($formValue('result_type_'.$slot, $match->{'result_type_'.$slot}) === 'extreme')>Extreme (3 pts)</option>
                                        </select>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button class="border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-amber-100 transition hover:bg-amber-500/20">Save Match Result</button>
                </form>
            @endif
        </article>
    </template>
