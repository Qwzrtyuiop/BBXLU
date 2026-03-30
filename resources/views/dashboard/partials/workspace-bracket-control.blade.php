<article class="border border-slate-800/80 bg-slate-950/55 p-2.5">
    @php
        $participantDecks = $selectedEventParticipants->keyBy('player_id');
        $swissRoundCount = $selectedEventRounds->where('stage', 'swiss')->count();
        $singleElimRoundCount = $selectedEventRounds->where('stage', 'single_elim')->count();
        $swissRounds = $selectedEventRounds->where('stage', 'swiss')->sortBy('round_number')->values();
        $eliminationRounds = $selectedEvent->usesSwissBracket()
            ? $selectedEventRounds->where('stage', 'single_elim')->sortBy('round_number')->values()
            : $selectedEventRounds->sortBy('round_number')->values();
        $deckMissingCount = $selectedMissingDeckRegistrations->count();
        $scheduledSwissRounds = $selectedEvent->usesSwissBracket() ? max(1, (int) ($selectedEvent->swiss_rounds ?? 1)) : 0;
        $lastSwissRound = $swissRounds->last();
        $workspaceMatchErrorId = (int) old('match_id', 0);
        $workspaceMatchErrorContext = $workspaceMatchErrorId > 0
            ? $selectedEventRounds
                ->flatMap(fn ($round) => $round->matches->map(fn ($match) => ['match' => $match, 'round' => $round]))
                ->first(fn (array $item) => (int) $item['match']->id === $workspaceMatchErrorId)
            : null;
        $workspaceMatchErrorTemplateId = $workspaceMatchErrorContext
            ? 'workspace-match-template-'.$workspaceMatchErrorContext['match']->id
            : null;
        $workspaceMatchErrorTitle = $workspaceMatchErrorContext
            ? 'Match '.($workspaceMatchErrorContext['match']->match_number ?: 1)
            : '';
        $workspaceMatchErrorSubtitle = $workspaceMatchErrorContext
            ? ($workspaceMatchErrorContext['round']->label
                ?: ucfirst(str_replace('_', ' ', $workspaceMatchErrorContext['round']->stage)).' Round '.$workspaceMatchErrorContext['round']->round_number)
            : '';
        $showSwissWaitingColumn = $selectedEvent->usesSwissBracket()
            && $selectedEvent->bracket_status !== 'completed'
            && $singleElimRoundCount === 0
            && $swissRoundCount < $scheduledSwissRounds;
        $showTopCutWaitingColumn = $selectedEvent->usesSwissBracket()
            && $selectedEvent->bracket_status !== 'completed'
            && $singleElimRoundCount === 0
            && $swissRoundCount >= $scheduledSwissRounds
            && $swissRounds->isNotEmpty();
        $eliminationBaseMatchCount = max(1, (int) ($eliminationRounds->first()?->matches->count() ?? 0));
        $eliminationGridRows = max(1, ($eliminationBaseMatchCount * 2) - 1);
        $eliminationRowHeightRem = 4.75;
        $nextSwissRoundNumber = $swissRoundCount + 1;
        $swissWaitingTitle = $swissRounds->isEmpty() ? 'Round 1' : 'Round '.$nextSwissRoundNumber;
        $swissWaitingDetail = match (true) {
            $swissRounds->isEmpty() => 'Generate the opening round to start Swiss.',
            $lastSwissRound && $lastSwissRound->status !== 'completed' => 'Current Swiss matches are still being played.',
            default => 'Waiting for next round generation.',
        };
        $generateButtonLabel = match (true) {
            $selectedEvent->bracket_status === 'completed' => 'Bracket Complete',
            $selectedEvent->usesSwissBracket() && $singleElimRoundCount === 0 && $swissRoundCount < $scheduledSwissRounds => 'Generate Swiss Round '.$nextSwissRoundNumber,
            $selectedEvent->usesSwissBracket() && $singleElimRoundCount === 0 => 'Generate Top Cut',
            $singleElimRoundCount === 0 => 'Generate Elimination Round 1',
            default => 'Generate Elimination Round '.($singleElimRoundCount + 1),
        };
        $showGenerateRoundButton = $selectedEvent->bracket_status !== 'completed'
            && $singleElimRoundCount === 0;
        $canGenerateRoundNow = $selectedEvent->bracket_status !== 'completed'
            && $deckMissingCount === 0
            && $pendingMatchCount === 0;
        $phaseLabel = match (true) {
            $selectedEvent->bracket_status === 'completed' => 'completed',
            $singleElimRoundCount > 0 => 'single elimination',
            $swissRoundCount > 0 => 'swiss',
            default => 'registration',
        };
        $nextGateLabel = match (true) {
            $selectedEvent->bracket_status === 'completed' => 'Event concluded',
            $deckMissingCount > 0 => 'Register '.$deckMissingCount.' '.\Illuminate\Support\Str::plural('deck', $deckMissingCount),
            $pendingMatchCount > 0 => 'Record '.$pendingMatchCount.' pending '.\Illuminate\Support\Str::plural('match', $pendingMatchCount),
            $selectedEventRoundCount === 0 => 'Generate opening round',
            $selectedEvent->usesSwissBracket() && $singleElimRoundCount === 0 => 'Generate top cut',
            default => 'Elimination auto-advance armed',
        };
        $nextGateDetail = match (true) {
            $selectedEvent->bracket_status === 'completed' => 'Placements and auto awards are final.',
            $deckMissingCount > 0 => $selectedEvent->usesLockedDecks()
                ? 'Locked decks are required before round 1 can start.'
                : 'Swiss is done. Save deck lists for elimination entrants before top cut.',
            $pendingMatchCount > 0 => 'Keep recording results until the current round closes.',
            $selectedEventRoundCount === 0 => 'Registration is ready for bracket generation.',
            $selectedEvent->usesSwissBracket() && $singleElimRoundCount === 0 => 'Swiss is complete. Seed the elimination field from standings.',
            default => 'Elimination rounds advance automatically when the current bracket column is complete.',
        };
    @endphp

    <div class="grid gap-2 xl:grid-cols-[minmax(0,1fr)_14rem]">
        <section class="border border-slate-800/80 bg-slate-950/72 px-3 py-2.5">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[9px] uppercase tracking-[0.16em] text-cyan-300/80">Workspace / {{ strtoupper($phaseLabel) }}</p>
                    <h4 class="mt-1 break-words text-[1.1rem] font-semibold leading-tight text-slate-50">{{ $selectedEvent->title }}</h4>
                    <p class="mt-1 text-[11px] text-slate-400">
                        {{ $selectedEvent->date->format('d M Y') }}
                        / {{ $selectedEvent->eventType->name }}
                        / {{ $selectedEventBracketLabel }}
                        / {{ $selectedEvent->usesLockedDecks() ? 'locked deck' : 'open until single elimination' }}
                    </p>
                    @if ($selectedEvent->location)
                        <p class="mt-1 text-[11px] text-slate-500">{{ $selectedEvent->location }}</p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-1.5">
                    @if ($showGenerateRoundButton)
                        <form action="{{ route('events.bracket.generate', $selectedEvent) }}" method="POST">
                            @csrf
                            <input type="hidden" name="dashboard_redirect" value="1">
                            <input type="hidden" name="dashboard_panel" value="workspace">
                            <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                            <button
                                @disabled(! $canGenerateRoundNow)
                                class="border px-2.5 py-1 text-[8px] font-semibold uppercase tracking-[0.14em] transition {{ $canGenerateRoundNow ? 'border-emerald-500/60 bg-emerald-500/10 text-emerald-100 hover:bg-emerald-500/20' : 'cursor-not-allowed border-slate-700 bg-slate-900/70 text-slate-500' }}"
                            >
                                {{ $generateButtonLabel }}
                            </button>
                        </form>
                    @else
                        <div class="border border-cyan-500/35 bg-cyan-500/10 px-2.5 py-1 text-[8px] font-semibold uppercase tracking-[0.14em] text-cyan-100">
                            Elimination Auto-Advance
                        </div>
                    @endif
                    @if ($canReshuffleSelectedSwissRoundOne)
                        <form action="{{ route('events.bracket.generate', $selectedEvent) }}" method="POST">
                            @csrf
                            <input type="hidden" name="reshuffle" value="1">
                            <input type="hidden" name="dashboard_redirect" value="1">
                            <input type="hidden" name="dashboard_panel" value="workspace">
                            <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                            <button class="border border-cyan-500/60 bg-cyan-500/10 px-2.5 py-1 text-[8px] font-semibold uppercase tracking-[0.14em] text-cyan-100 transition hover:bg-cyan-500/20">Reshuffle</button>
                        </form>
                    @endif
                    <a href="{{ route('dashboard', ['panel' => 'events', 'event' => $selectedEvent->id]) }}" class="border border-slate-700 px-2.5 py-1 text-[8px] font-semibold uppercase tracking-[0.14em] text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200">{{ $selectedEvent->hasStarted() ? 'View Event' : 'Edit Event' }}</a>
                </div>
            </div>

            <div class="mt-2 grid gap-1 sm:grid-cols-3 xl:grid-cols-6">
                @foreach ([
                    ['label' => 'Players', 'value' => $selectedEventParticipants->count(), 'tone' => 'text-amber-200'],
                    ['label' => 'Rounds', 'value' => $selectedEventRoundCount, 'tone' => 'text-amber-200'],
                    ['label' => 'Pending', 'value' => $pendingMatchCount, 'tone' => 'text-amber-200'],
                    ['label' => 'Done', 'value' => $completedMatchCount, 'tone' => 'text-amber-200'],
                    ['label' => 'Swiss', 'value' => $swissRoundCount, 'tone' => 'text-amber-200'],
                    ['label' => 'Deck Gate', 'value' => $deckMissingCount, 'tone' => $deckMissingCount > 0 ? 'text-rose-200' : 'text-emerald-200'],
                ] as $metric)
                    <div class="border border-slate-800 bg-slate-900/70 px-2 py-1.5">
                        <p class="text-[9px] uppercase tracking-[0.14em] text-slate-500">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-[14px] font-semibold leading-none {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <aside class="border border-cyan-400/20 bg-[linear-gradient(180deg,rgba(8,47,73,0.12)_0%,rgba(2,6,23,0.9)_100%)] px-3 py-2.5">
            <p class="text-[9px] uppercase tracking-[0.16em] text-cyan-300/80">Next Gate</p>
            <p class="mt-1 text-[13px] font-semibold leading-tight text-slate-100">{{ $nextGateLabel }}</p>
            <p class="mt-1 text-[11px] leading-snug text-slate-500">{{ $nextGateDetail }}</p>

            <div class="mt-3 border-t border-slate-800 pt-2">
                <p class="text-[9px] uppercase tracking-[0.16em] text-slate-500">Flow</p>
                <p class="mt-1 text-[11px] leading-snug text-slate-400">Register players, complete Swiss, seed top cut, finish elimination, conclude event.</p>
            </div>
        </aside>
    </div>

    @if ($selectedEvent->usesSwissBracket() && $selectedSwissStandings->isNotEmpty())
        <section class="mt-2.5 border border-cyan-400/20 bg-slate-950/58 p-2.5">
            <div class="flex items-center justify-between gap-2">
                <h5 class="text-[12px] font-semibold uppercase tracking-[0.12em] text-cyan-100">Swiss Standings</h5>
                <span class="text-[9px] uppercase tracking-[0.14em] text-slate-500">{{ $selectedSwissStandings->count() }} players</span>
            </div>

            <div class="mt-2 max-h-[12rem] overflow-auto">
                <table class="min-w-full text-left text-[11px]">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-500">
                            <th class="px-2 py-1.5">#</th>
                            <th class="px-2 py-1.5">Player</th>
                            <th class="px-2 py-1.5">W-L</th>
                            <th class="px-2 py-1.5">Byes</th>
                            <th class="px-2 py-1.5">Score</th>
                            <th class="px-2 py-1.5">Diff</th>
                            <th class="px-2 py-1.5">Pts</th>
                            <th class="px-2 py-1.5">Buch.</th>
                            <th class="px-2 py-1.5">Hist</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($selectedSwissStandings as $row)
                            <tr class="border-b border-slate-900/80">
                                <td class="px-2 py-1.5 font-semibold text-amber-200">{{ $row['rank'] }}</td>
                                <td class="px-2 py-1.5 font-medium text-slate-100">{{ $row['player']->user->nickname }}</td>
                                <td class="px-2 py-1.5 text-slate-300">{{ $row['wins'] }}-{{ $row['losses'] }}</td>
                                <td class="px-2 py-1.5 text-slate-300">{{ $row['byes'] }}</td>
                                <td class="px-2 py-1.5 text-slate-300">{{ number_format($row['match_points'], 1) }}</td>
                                <td class="px-2 py-1.5 text-slate-300">{{ $row['points_diff'] }}</td>
                                <td class="px-2 py-1.5 text-slate-300">{{ $row['battle_points'] }}</td>
                                <td class="px-2 py-1.5 text-slate-300">{{ number_format($row['buchholz'], 1) }}</td>
                                <td class="px-2 py-1.5">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($row['history'] as $historyItem)
                                            <span class="rounded px-1.5 py-0.5 text-[9px] font-semibold {{ $historyItem === 'W' ? 'bg-cyan-500/20 text-cyan-200' : ($historyItem === 'L' ? 'bg-rose-500/20 text-rose-200' : 'bg-amber-500/20 text-amber-200') }}">
                                                {{ $historyItem }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <div class="mt-2.5 space-y-2">
        @if ($selectedEvent->usesSwissBracket())
            <section class="border border-cyan-400/20 bg-slate-950/62 p-2.5">
                <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2">
                    <div>
                        <h5 class="text-[12px] font-semibold uppercase tracking-[0.12em] text-cyan-100">Swiss Matches</h5>
                        <p class="mt-1 text-[10px] uppercase tracking-[0.12em] text-slate-500">Rounds display left to right. Open any match to record results.</p>
                    </div>
                    <span class="rounded border border-slate-800 bg-slate-900/70 px-2 py-1 text-[9px] uppercase tracking-[0.14em] text-slate-400">{{ $swissRoundCount }}/{{ $scheduledSwissRounds }} rounds</span>
                </div>

                <div class="mt-2 overflow-x-auto pb-1">
                    <div class="flex min-w-max items-start gap-2">
                        @foreach ($swissRounds as $round)
                            <section class="w-[13.25rem] shrink-0 border border-slate-800/80 bg-slate-950/82">
                                <div class="border-b border-slate-800/80 bg-slate-900/80 px-2.5 py-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-[11px] font-semibold text-slate-100">{{ $round->label ?: 'Round '.$round->round_number }}</p>
                                        <span class="rounded border {{ $round->status === 'completed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-200' : 'border-amber-500/40 bg-amber-500/10 text-amber-200' }} px-1.5 py-0.5 text-[8px] uppercase tracking-[0.14em]">{{ $round->status }}</span>
                                    </div>
                                </div>

                                <div class="space-y-1 p-1.25">
                                    @foreach ($round->matches->sortBy('match_number')->values() as $match)
                                        @include('dashboard.partials.workspace-match-card', [
                                            'match' => $match,
                                            'round' => $round,
                                            'layout' => 'swiss',
                                            'matchIndex' => $loop->iteration,
                                        ])
                                    @endforeach
                                </div>
                            </section>
                        @endforeach

                        @if ($showSwissWaitingColumn)
                            <section class="flex min-h-[14rem] w-[13.25rem] shrink-0 flex-col border border-dashed border-slate-700/80 bg-slate-950/55">
                                <div class="border-b border-dashed border-slate-700/80 bg-slate-900/65 px-2.5 py-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-[11px] font-semibold text-slate-300">{{ $swissWaitingTitle }}</p>
                                        <span class="rounded border border-slate-700/80 px-1.5 py-0.5 text-[8px] uppercase tracking-[0.14em] text-slate-500">waiting</span>
                                    </div>
                                </div>

                                <div class="flex flex-1 items-center justify-center p-3 text-center">
                                    <div class="space-y-1.5">
                                        <p class="text-[10px] uppercase tracking-[0.16em] text-slate-500">Next Round</p>
                                        <p class="text-sm font-semibold leading-tight text-slate-200">{{ $swissWaitingDetail }}</p>
                                        <p class="text-[11px] text-slate-500">Once the gate clears, the board continues here.</p>
                                    </div>
                                </div>
                            </section>
                        @elseif ($showTopCutWaitingColumn)
                            <section class="flex min-h-[14rem] w-[13.25rem] shrink-0 flex-col border border-dashed border-amber-500/45 bg-amber-500/[0.05]">
                                <div class="border-b border-dashed border-amber-500/35 bg-slate-900/65 px-2.5 py-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-[11px] font-semibold text-amber-100">Top Cut</p>
                                        <span class="rounded border border-amber-500/35 px-1.5 py-0.5 text-[8px] uppercase tracking-[0.14em] text-amber-200">waiting</span>
                                    </div>
                                </div>

                                <div class="flex flex-1 items-center justify-center p-3 text-center">
                                    <div class="space-y-1.5">
                                        <p class="text-[10px] uppercase tracking-[0.16em] text-amber-200/80">Swiss Complete</p>
                                        <p class="text-sm font-semibold leading-tight text-slate-100">{{ $deckMissingCount > 0 ? 'Deck registration is still blocking top cut generation.' : 'Waiting for top cut generation.' }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $deckMissingCount > 0 ? 'Finish the deck gate, then generate elimination.' : 'The elimination bracket will appear here next.' }}</p>
                                    </div>
                                </div>
                            </section>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        @if ($eliminationRounds->isNotEmpty())
            <section class="border border-amber-400/20 bg-slate-950/62 p-2.5">
                <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2">
                    <div>
                        <h5 class="text-[12px] font-semibold uppercase tracking-[0.12em] text-amber-100">Elimination Rounds</h5>
                        <p class="mt-1 text-[10px] uppercase tracking-[0.12em] text-slate-500">Top cut and bracket finish flow.</p>
                    </div>
                    <span class="rounded border border-slate-800 bg-slate-900/70 px-2 py-1 text-[9px] uppercase tracking-[0.14em] text-slate-400">{{ $eliminationRounds->count() }} rounds</span>
                </div>

                <div class="mt-2 overflow-x-auto pb-1">
                    <div class="min-w-max">
                        <div
                            class="grid gap-8"
                            style="grid-template-columns: repeat({{ max(1, $eliminationRounds->count()) }}, minmax(0, 13.25rem));"
                        >
                            @foreach ($eliminationRounds as $round)
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-[11px] font-semibold text-slate-100">{{ $round->label ?: 'Round '.$round->round_number }}</p>
                                        <span class="rounded border {{ $round->status === 'completed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-200' : 'border-amber-500/40 bg-amber-500/10 text-amber-200' }} px-1.5 py-0.5 text-[8px] uppercase tracking-[0.14em]">{{ $round->status }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div
                            class="mt-2 grid gap-8 overflow-visible"
                            style="
                                grid-template-columns: repeat({{ max(1, $eliminationRounds->count()) }}, minmax(0, 13.25rem));
                                grid-template-rows: repeat({{ $eliminationGridRows }}, minmax(0, {{ $eliminationRowHeightRem }}rem));
                            "
                        >
                            @foreach ($eliminationRounds as $round)
                                @php
                                    $roundIndex = $loop->index;
                                    $step = 2 ** $roundIndex;
                                    $sortedMatches = $round->matches->sortBy('match_number')->values();
                                    $hasNextRound = $roundIndex < ($eliminationRounds->count() - 1);
                                @endphp

                                @foreach ($sortedMatches as $match)
                                    @php
                                        $gridRowStart = $step + ($loop->index * ($step * 2));
                                        $connectorDirection = $loop->index % 2 === 0 ? 'down' : 'up';
                                    @endphp
                                    @include('dashboard.partials.workspace-match-card', [
                                        'match' => $match,
                                        'round' => $round,
                                        'layout' => 'bracket',
                                        'matchIndex' => $loop->iteration,
                                        'gridColumn' => $roundIndex + 1,
                                        'gridRowStart' => $gridRowStart,
                                        'showBracketConnector' => $roundIndex > 0,
                                        'bracketConnectorHeightRem' => $roundIndex > 0 ? ($step * $eliminationRowHeightRem) : 0,
                                        'showBracketOutgoingConnector' => $hasNextRound,
                                        'bracketOutgoingDirection' => $connectorDirection,
                                        'bracketOutgoingHalfHeightRem' => $step * $eliminationRowHeightRem,
                                    ])
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @elseif (! $selectedEvent->usesSwissBracket())
            <div class="border border-slate-800/80 bg-slate-950/65 px-3 py-3">
                <p class="text-sm text-slate-400">No bracket rounds generated yet. Add participants, then generate the opening round.</p>
            </div>
        @endif
    </div>

    <article class="mt-2.5 border border-slate-800/80 bg-slate-950/60 p-2.5">
        <div class="flex items-center justify-between gap-2">
            <h4 class="text-[12px] font-semibold uppercase tracking-[0.12em] text-cyan-100">Auto Awards</h4>
            <div class="flex flex-wrap items-center justify-end gap-1.5">
                <span class="text-[9px] uppercase tracking-[0.14em] text-slate-500">{{ $selectedEventAwards->count() }} assigned</span>
                @if ($selectedEvent->bracket_status === 'completed')
                    <form action="{{ route('events.outcomes.regenerate', $selectedEvent) }}" method="POST">
                        @csrf
                        <input type="hidden" name="dashboard_redirect" value="1">
                        <input type="hidden" name="dashboard_panel" value="workspace">
                        <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                        <button class="border border-cyan-500/60 bg-cyan-500/10 px-2 py-1 text-[8px] font-semibold uppercase tracking-[0.14em] text-cyan-100 transition hover:bg-cyan-500/20">
                            Regenerate Awards
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if ($selectedEvent->bracket_status === 'completed')
            <p class="mt-2 text-[11px] text-slate-500">Rebuild placements and awards from the finished bracket if standings or completed match data changed.</p>
        @endif

        <div class="mt-2 flex flex-wrap gap-1.5">
            @forelse ($selectedEventAwards as $eventAward)
                <div class="flex items-center gap-1.5 border border-slate-800/80 bg-slate-950/65 px-2 py-1 text-[11px]">
                    <span class="rounded border border-cyan-500/40 px-1.5 py-0.5 font-semibold text-cyan-200">{{ $eventAward->award->name }}</span>
                    <span class="text-slate-200">{{ $eventAward->player->user->nickname }}</span>
                </div>
            @empty
                <p class="text-[11px] text-slate-500">{{ $selectedEvent->bracket_status === 'completed' ? 'No auto awards were generated.' : 'Awards appear after the event concludes.' }}</p>
            @endforelse
        </div>
    </article>

    <div
        data-workspace-match-modal
        data-open-template-id="{{ $workspaceMatchErrorTemplateId }}"
        data-open-title="{{ $workspaceMatchErrorTitle }}"
        data-open-subtitle="{{ $workspaceMatchErrorSubtitle }}"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4"
    >
        <div class="max-h-[90vh] w-full max-w-6xl overflow-y-auto border border-cyan-400/60 bg-slate-950 p-4 sm:p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <p class="type-kicker text-xs text-cyan-300" data-workspace-match-modal-subtitle></p>
                    <h3 class="type-headline mt-1 text-xl text-white sm:text-2xl" data-workspace-match-modal-title></h3>
                </div>
                <button type="button" data-workspace-match-close class="type-label border border-slate-700 px-3 py-1.5 text-[10px] text-slate-100 transition hover:border-rose-500 hover:text-rose-200">Close</button>
            </div>

            <div data-workspace-match-modal-body></div>
        </div>
    </div>
</article>
