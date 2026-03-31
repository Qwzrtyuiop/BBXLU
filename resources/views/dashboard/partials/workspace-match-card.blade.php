@php
    $layout = $layout ?? 'default';
    $matchIndex = $matchIndex ?? 1;
    $matchLabel = 'Match '.($match->match_number ?: $matchIndex);
    $roundLabel = $round->label ?: ucfirst(str_replace('_', ' ', $round->stage)).' Round '.$round->round_number;
    $matchTemplateId = 'workspace-match-template-'.$match->id;
    $isCompleted = $match->status === 'completed';
    $hasPlaceholderOpponent = ! $match->is_bye && ! $match->player2_id;
    $player1Name = $match->player1->user->nickname;
    $player2Name = $match->is_bye
        ? 'BYE'
        : ($match->player2_id ? $match->player2->user->nickname : '- opponent');
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
        : ($isCompleted ? 'text-cyan-200' : ($hasPlaceholderOpponent ? 'text-slate-300' : 'text-amber-200'));
    $stateLabel = $match->is_bye
        ? 'Bye'
        : ($isCompleted ? $match->player1_score.'-'.$match->player2_score : ($hasPlaceholderOpponent ? 'Waiting' : 'Open'));
    $footerLabel = $match->is_bye
        ? 'auto advance'
        : ($isCompleted ? 'winner: '.($match->winner?->user->nickname ?? 'TBD') : ($hasPlaceholderOpponent ? 'waiting for opponent' : 'record result'));
    $joinedCardClasses = 'overflow-hidden rounded border border-slate-800/90 bg-slate-900/65';
    $winnerRowClasses = 'bg-cyan-400/[0.08] font-semibold text-slate-50';
    $winnerMetricClasses = 'text-cyan-100';
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
            class="block w-full px-0 py-1 text-left transition hover:opacity-95 focus:outline-none focus-visible:ring-1 focus-visible:ring-cyan-400/55"
        >
            <div class="{{ $joinedCardClasses }}">
                <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 px-2.5 py-1.5 text-[10px] leading-tight {{ $player1Won ? $winnerRowClasses : 'text-slate-200' }}">
                    <span class="truncate">{{ $player1Name }}</span>
                    <span class="text-right text-[9px] font-semibold {{ $player1Won ? $winnerMetricClasses : 'text-slate-400' }}">{{ $player1Metric }}</span>
                </div>
                <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 border-t border-slate-800/90 px-2.5 py-1.5 text-[10px] leading-tight {{ $player2Won ? $winnerRowClasses : 'text-slate-300' }}">
                    <span class="truncate">{{ $player2Name }}</span>
                    <span class="text-right text-[9px] font-semibold {{ $player2Won ? $winnerMetricClasses : 'text-slate-400' }}">{{ $player2Metric }}</span>
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

            <div class="{{ $joinedCardClasses }}">
                <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 px-2 py-1 text-[10px] leading-tight {{ $player1Won ? $winnerRowClasses : 'text-slate-200' }}">
                    <span class="truncate">{{ $player1Name }}</span>
                    <span class="text-right text-[9px] font-semibold {{ $player1Won ? $winnerMetricClasses : 'text-slate-400' }}">{{ $player1Metric }}</span>
                </div>
                <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 border-t border-slate-800/90 px-2 py-1 text-[10px] leading-tight {{ $player2Won ? $winnerRowClasses : 'text-slate-300' }}">
                    <span class="truncate">{{ $player2Name }}</span>
                    <span class="text-right text-[9px] font-semibold {{ $player2Won ? $winnerMetricClasses : 'text-slate-400' }}">{{ $player2Metric }}</span>
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
            $matchWinThreshold = $selectedEvent->battleWinThresholdForMatch($match, $round, $round->stage, $round->matches->count());
            $battleSlotCount = $selectedEvent->maxBattleSlotsForThreshold($matchWinThreshold);
            $isReopenedMatch = (int) old('match_id', 0) === $match->id;
            $formValue = fn (string $key, mixed $default = null) => $isReopenedMatch ? old($key, $default) : $default;
            $matchErrorMessages = $isReopenedMatch
                ? collect(array_merge(
                    ['match_scores', 'match_deck', 'match_players', 'player2_id', 'player1_stadium_side', 'player2_stadium_side'],
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
            $player1SelectedSide = $formValue('player1_stadium_side', $match->player1StadiumSide?->code);
            $player2SelectedSide = $formValue('player2_stadium_side', $match->player2StadiumSide?->code);
            $matchStatusLabel = $match->is_bye ? 'Auto Bye' : ucfirst(str_replace('_', ' ', $match->status));
            $summaryToneClasses = $match->is_bye
                ? 'border-emerald-400/30 bg-emerald-400/[0.06] text-emerald-100'
                : ($match->status === 'completed'
                    ? 'border-cyan-400/30 bg-cyan-400/[0.06] text-cyan-100'
                    : ($hasPlaceholderOpponent
                        ? 'border-slate-700/90 bg-slate-900/70 text-slate-200'
                        : 'border-amber-400/30 bg-amber-400/[0.06] text-amber-100'));
            $playerSetupCards = [
                [
                    'slot' => 1,
                    'title' => $player1Name,
                    'participant' => $player1Participant,
                    'selectedSide' => $player1SelectedSide,
                    'storedDeck' => [$match->player1_bey1, $match->player1_bey2, $match->player1_bey3],
                ],
                [
                    'slot' => 2,
                    'title' => $player2Name,
                    'participant' => $player2Participant,
                    'selectedSide' => $player2SelectedSide,
                    'storedDeck' => [$match->player2_bey1, $match->player2_bey2, $match->player2_bey3],
                ],
            ];
        @endphp
        <article class="space-y-4">
            <div class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-start">
                <div class="rounded-2xl border border-cyan-500/30 bg-slate-950/75 p-3.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] {{ $summaryToneClasses }}">
                            {{ $matchStatusLabel }}
                        </span>
                        @if (! $match->is_bye && ! $hasPlaceholderOpponent)
                            <span class="rounded-full border border-slate-700/80 bg-slate-900/70 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-300">
                                First To {{ $matchWinThreshold }}
                            </span>
                        @endif
                        @if ($match->stage === 'single_elim')
                            <span class="rounded-full border border-slate-700/80 bg-slate-900/70 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-300">
                                Top Cut
                            </span>
                        @endif
                    </div>

                    <div class="mt-3 grid gap-2.5 lg:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] lg:items-center">
                        <div class="rounded-xl border border-slate-800/85 bg-slate-900/70 px-3 py-2.5">
                            <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">P1</p>
                            <p class="mt-1 truncate text-base font-semibold text-slate-50">{{ $player1Name }}</p>
                        </div>
                        <div class="flex items-center justify-center text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">
                            VS
                        </div>
                        <div class="rounded-xl border border-slate-800/85 bg-slate-900/70 px-3 py-2.5">
                            <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">P2</p>
                            <p class="mt-1 truncate text-base font-semibold text-slate-50">{{ $player2Name }}</p>
                        </div>
                    </div>

                    @if (! $match->is_bye && $match->status !== 'completed' && ! $hasPlaceholderOpponent)
                        <p class="mt-2.5 text-[10px] text-slate-400">Set side, confirm Bey picks, then record battles until one player reaches {{ $matchWinThreshold }} points.</p>
                    @endif
                </div>

                <form action="{{ route('events.matches.destroy', [$selectedEvent, $match]) }}" method="POST" onsubmit="return confirm('Delete this match?');" class="xl:pt-1">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="dashboard_redirect" value="1">
                    <input type="hidden" name="dashboard_panel" value="workspace">
                    <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                    <button class="w-full rounded-xl border border-rose-500/60 px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-rose-200 transition hover:bg-rose-500/10 xl:w-auto">Delete Match</button>
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
            @elseif ($hasPlaceholderOpponent)
                <div class="grid gap-3 xl:grid-cols-2">
                    <div class="border border-slate-800 bg-slate-900/60 px-3 py-3">
                        <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Waiting</p>
                        <p class="mt-1 text-sm text-slate-100">This bracket slot is ready for {{ $player1Name }}.</p>
                        <p class="mt-2 text-[11px] text-slate-500">The opponent appears here as soon as the other feeder match is decided.</p>
                    </div>
                    <div class="border border-slate-800 bg-slate-900/60 px-3 py-3">
                        <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Sources</p>
                        <div class="mt-2 space-y-1.5 text-[11px] text-slate-300">
                            <p>Source A: {{ $match->sourceMatch1?->match_number ? 'Match '.$match->sourceMatch1->match_number : 'Pending feeder' }}</p>
                            <p>Source B: {{ $match->sourceMatch2?->match_number ? 'Match '.$match->sourceMatch2->match_number : '- opponent' }}</p>
                        </div>
                    </div>
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

                    <div class="grid gap-3.5 xl:grid-cols-[minmax(14.5rem,0.72fr)_minmax(0,1.58fr)] xl:items-start">
                        <section class="grid gap-2.5" data-stadium-side-control>
                            @foreach ($playerSetupCards as $setupCard)
                                @php
                                    $selectedSide = in_array($setupCard['selectedSide'], ['X', 'B'], true) ? $setupCard['selectedSide'] : null;
                                    $registeredBeys = $setupCard['participant'] ? array_values(array_filter($setupCard['participant']->registeredBeys())) : [];
                                @endphp
                                <div class="rounded-2xl border border-slate-800/85 bg-slate-950/70 p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Player {{ $setupCard['slot'] }}</p>
                                            <h4 class="mt-1 truncate text-[13px] font-semibold text-slate-50">{{ $setupCard['title'] }}</h4>
                                        </div>
                                        @if ($usesRegisteredDecks)
                                            <span class="rounded-full border border-cyan-500/35 bg-cyan-500/[0.06] px-2 py-1 text-[9px] font-semibold uppercase tracking-[0.14em] text-cyan-200">Registered</span>
                                        @endif
                                    </div>

                                    @if ($usesRegisteredDecks)
                                        <div class="mt-2.5">
                                            <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Bey Picks</p>
                                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                                @forelse ($registeredBeys as $bey)
                                                    <span class="rounded-full border border-slate-700/80 bg-slate-900/75 px-2 py-0.5 text-[10px] text-slate-200">{{ $bey }}</span>
                                                @empty
                                                    <span class="text-[11px] text-slate-500">No deck registered yet.</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-2 grid gap-1">
                                            <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Bey Picks</p>
                                            <div class="grid grid-cols-3 gap-1.5">
                                                @foreach ([1, 2, 3] as $beySlot)
                                                    <label class="grid gap-1">
                                                        <span class="text-[9px] uppercase tracking-[0.14em] text-slate-500">B{{ $beySlot }}</span>
                                                        <input
                                                            name="player{{ $setupCard['slot'] }}_bey{{ $beySlot }}"
                                                            value="{{ $formValue('player'.$setupCard['slot'].'_bey'.$beySlot, $setupCard['storedDeck'][$beySlot - 1]) }}"
                                                            placeholder="Pick"
                                                            class="rounded-lg border border-slate-700/80 bg-slate-950/80 px-2 py-1.5 text-[12px] text-slate-100 placeholder:text-slate-600 focus:border-amber-500 focus:outline-none"
                                                        >
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-2.5 grid gap-1.5" data-stadium-side-group data-player-slot="{{ $setupCard['slot'] }}">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Stadium Side</span>
                                            <span class="text-[10px] uppercase tracking-[0.14em] text-slate-600">Auto-opposite</span>
                                        </div>
                                        <input type="hidden" name="player{{ $setupCard['slot'] }}_stadium_side" value="{{ $selectedSide ?? '' }}" data-stadium-side-input>
                                        <div class="grid grid-cols-2 gap-1.5">
                                            @foreach (['X', 'B'] as $sideCode)
                                                @php
                                                    $isSelected = $selectedSide === $sideCode;
                                                @endphp
                                                <button
                                                    type="button"
                                                    data-stadium-side-choice
                                                    data-side-choice="{{ $sideCode }}"
                                                    class="rounded-xl border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.18em] transition {{ $isSelected ? 'border-cyan-400/70 bg-cyan-400/10 text-cyan-100' : 'border-slate-700/80 bg-slate-950/70 text-slate-300 hover:border-cyan-400/45 hover:text-cyan-100' }}"
                                                >
                                                    {{ $sideCode }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </section>

                        <section class="rounded-2xl border border-slate-800/85 bg-slate-950/70 p-3">
                            <div class="flex flex-wrap items-end justify-between gap-3">
                                <div>
                                    <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Battle Results</p>
                                    <p class="mt-1 text-[10px] text-slate-400">First to {{ $matchWinThreshold }}. Spin = 1, Over = 2, Burst = 2, Extreme = 3.</p>
                                </div>
                                <div class="grid gap-1 rounded-xl border border-slate-800/85 bg-slate-900/70 px-2.5 py-1.5 text-[9px] text-slate-300 sm:grid-cols-2 sm:gap-2.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full border border-cyan-500/35 bg-cyan-500/[0.08] px-1 text-[8px] font-semibold text-cyan-200">P1</span>
                                        <span class="truncate">{{ $player1Name }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full border border-slate-700/85 bg-slate-950/80 px-1 text-[8px] font-semibold text-slate-200">P2</span>
                                        <span class="truncate">{{ $player2Name }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2.5 grid gap-2 md:grid-cols-2 xl:grid-cols-4">
                                @foreach (range(1, $battleSlotCount) as $slot)
                                    @include('partials.battle-result-picker', [
                                        'slot' => $slot,
                                        'player1Name' => $player1Name,
                                        'player2Name' => $player2Name,
                                        'selectedWinner' => $formValue('result_'.$slot, $match->{'result_'.$slot}),
                                        'selectedType' => $formValue('result_type_'.$slot, $match->{'result_type_'.$slot}),
                                    ])
                                @endforeach
                            </div>

                            <div class="mt-3 flex justify-end">
                                <button class="rounded-xl border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-amber-100 transition hover:bg-amber-500/20">Save Match Result</button>
                            </div>
                        </section>
                    </div>
                </form>
            @endif
        </article>
    </template>
