@php
    $preview = $preview ?? [];
    $participants = $preview['participants'] ?? collect();
    $results = $preview['results'] ?? collect();
    $awards = $preview['awards'] ?? collect();
    $rounds = $preview['rounds'] ?? collect();
    $swissStandings = $preview['swissStandings'] ?? collect();
    $pendingMatchCount = $preview['pendingMatchCount'] ?? 0;
    $completedMatchCount = $preview['completedMatchCount'] ?? 0;
    $deckTargetCount = $preview['deckTargetCount'] ?? 0;
    $missingDeckCount = $preview['missingDeckCount'] ?? 0;

    $swissRounds = $rounds->where('stage', 'swiss')->sortBy('round_number')->values();
    $eliminationRounds = $event->usesSwissBracket()
        ? $rounds->where('stage', 'single_elim')->sortBy('round_number')->values()
        : $rounds->sortBy('round_number')->values();
    $swissRoundCount = $swissRounds->count();
    $singleElimRoundCount = $eliminationRounds->count();
    $roundCount = $rounds->count();
    $scheduledSwissRounds = $event->usesSwissBracket() ? max(1, (int) ($event->swiss_rounds ?? 1)) : 0;
    $lastSwissRound = $swissRounds->last();
    $showSwissWaitingColumn = $event->usesSwissBracket()
        && $event->bracket_status !== 'completed'
        && $singleElimRoundCount === 0
        && $swissRoundCount < $scheduledSwissRounds;
    $showTopCutWaitingColumn = $event->usesSwissBracket()
        && $event->bracket_status !== 'completed'
        && $singleElimRoundCount === 0
        && $swissRoundCount >= $scheduledSwissRounds
        && $swissRounds->isNotEmpty();
    $thirdPlaceRound = $eliminationRounds->last();
    $hasThirdPlaceBracket = $thirdPlaceRound && str_contains(strtolower((string) ($thirdPlaceRound->label ?? '')), '3rd place');
    $thirdPlaceMatch = $hasThirdPlaceBracket
        ? $thirdPlaceRound->matches->sortBy('match_number')->values()->get(1)
        : null;
    $nextSwissRoundNumber = $swissRoundCount + 1;
    $swissWaitingTitle = $swissRounds->isEmpty() ? 'Round 1' : 'Round '.$nextSwissRoundNumber;
    $swissWaitingDetail = match (true) {
        $swissRounds->isEmpty() => 'Opening round is not generated yet.',
        $lastSwissRound && $lastSwissRound->status !== 'completed' => 'Current Swiss matches are still being played.',
        default => 'Waiting for next round generation.',
    };
    $phaseLabel = match (true) {
        $event->bracket_status === 'completed' => 'completed',
        $singleElimRoundCount > 0 => 'single elimination',
        $swissRoundCount > 0 => 'swiss',
        default => 'registration',
    };
    $nextGateLabel = match (true) {
        $event->bracket_status === 'completed' => 'Event concluded',
        $missingDeckCount > 0 => 'Deck registration pending',
        $pendingMatchCount > 0 => 'Matches still pending',
        $roundCount === 0 => 'Waiting for bracket generation',
        $event->usesSwissBracket() && $singleElimRoundCount === 0 => 'Waiting for top cut',
        default => 'Bracket auto-advance armed',
    };
    $nextGateDetail = match (true) {
        $event->bracket_status === 'completed' => 'Placements and awards reflect the finished event state.',
        $missingDeckCount > 0 => $event->usesLockedDecks()
            ? 'Locked decks are still required before play can continue.'
            : 'Swiss is done. Beys 1-3 still need to be registered for elimination.',
        $pendingMatchCount > 0 => 'Record the remaining results to unlock the next stage.',
        $roundCount === 0 => 'Registration is ready, but no bracket rounds exist yet.',
        $event->usesSwissBracket() && $singleElimRoundCount === 0 => 'Swiss is complete. Generate top cut when ready.',
        default => 'The next elimination slot appears as soon as a feeder match produces a winner.',
    };
    $eliminationBaseMatchCount = max(1, (int) ($eliminationRounds->first()?->matches->count() ?? 0));
    $eliminationGridRows = max(1, ($eliminationBaseMatchCount * 2) - 1);
    $eliminationRowHeightRem = 4.75;
@endphp

<div class="grid gap-3 xl:grid-cols-[minmax(0,1.72fr)_minmax(18rem,.64fr)]">
    <div class="min-w-0 space-y-2.5">
        <div class="grid gap-2 xl:grid-cols-[minmax(0,1fr)_14rem]">
            <section class="border border-slate-800/80 bg-slate-950/72 px-3 py-2.5">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[9px] uppercase tracking-[0.16em] text-cyan-300/80">Event Preview / {{ strtoupper($phaseLabel) }}</p>
                        <h4 class="mt-1 break-words text-[1.1rem] font-semibold leading-tight text-slate-50">{{ $event->title }}</h4>
                        <p class="mt-1 text-[11px] text-slate-400">
                            {{ $event->date->format('d M Y') }}
                            / {{ $event->eventType->name }}
                            / {{ $event->bracketLabel() }}
                            / {{ $event->usesLockedDecks() ? 'locked deck' : 'open until single elimination' }}
                        </p>
                        @if ($event->location)
                            <p class="mt-1 text-[11px] text-slate-500">{{ $event->location }}</p>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-1.5">
                        <span class="border border-slate-700 px-2.5 py-1 text-[8px] font-semibold uppercase tracking-[0.14em] text-slate-200">{{ $event->status }}</span>
                        @if (($dashboardSessionActiveEventId ?? null) === $event->id)
                            <span class="border border-emerald-500/50 bg-emerald-500/10 px-2.5 py-1 text-[8px] font-semibold uppercase tracking-[0.14em] text-emerald-100">Active</span>
                        @endif
                        @if ($event->is_active)
                            <span class="border border-cyan-400/50 bg-cyan-500/10 px-2.5 py-1 text-[8px] font-semibold uppercase tracking-[0.14em] text-cyan-100">Live</span>
                        @endif
                        @if ($event->is_lock_deck)
                            <span class="border border-amber-500/50 bg-amber-500/10 px-2.5 py-1 text-[8px] font-semibold uppercase tracking-[0.14em] text-amber-100">Lock Deck</span>
                        @endif
                    </div>
                </div>

                <div class="mt-2 grid gap-1 sm:grid-cols-3 xl:grid-cols-6">
                    @foreach ([
                        ['label' => 'Players', 'value' => $participants->count(), 'tone' => 'text-amber-200'],
                        ['label' => 'Rounds', 'value' => $roundCount, 'tone' => 'text-amber-200'],
                        ['label' => 'Pending', 'value' => $pendingMatchCount, 'tone' => $pendingMatchCount > 0 ? 'text-amber-200' : 'text-emerald-200'],
                        ['label' => 'Done', 'value' => $completedMatchCount, 'tone' => 'text-amber-200'],
                        ['label' => 'Swiss', 'value' => $swissRoundCount, 'tone' => 'text-amber-200'],
                        ['label' => 'Lock Deck', 'value' => $event->usesLockedDecks() ? 'True' : 'False', 'tone' => $event->usesLockedDecks() ? 'text-emerald-200' : 'text-slate-300'],
                    ] as $metric)
                        <div class="border border-slate-800 bg-slate-900/70 px-2 py-1.5">
                            <p class="text-[9px] uppercase tracking-[0.14em] text-slate-500">{{ $metric['label'] }}</p>
                            <p class="mt-1 text-[14px] font-semibold leading-none {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-2 border-t border-slate-800/80 pt-2">
                    <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Description</p>
                    <p class="mt-1 text-[12px] leading-relaxed text-slate-300">{{ $event->description ?: 'No description provided.' }}</p>
                </div>
            </section>

            <aside class="border border-cyan-400/20 bg-[linear-gradient(180deg,rgba(8,47,73,0.12)_0%,rgba(2,6,23,0.9)_100%)] px-3 py-2.5">
                <p class="text-[9px] uppercase tracking-[0.16em] text-cyan-300/80">Next Gate</p>
                <p class="mt-1 text-[13px] font-semibold leading-tight text-slate-100">{{ $nextGateLabel }}</p>
                <p class="mt-1 text-[11px] leading-snug text-slate-500">{{ $nextGateDetail }}</p>

                <div class="mt-3 border-t border-slate-800 pt-2">
                    <p class="text-[9px] uppercase tracking-[0.16em] text-slate-500">Created By</p>
                    <p class="mt-1 text-[11px] leading-snug text-slate-300">{{ $event->creator?->nickname ?? 'Unknown user' }}</p>
                </div>

                <div class="mt-3 border-t border-slate-800 pt-2">
                    <p class="text-[9px] uppercase tracking-[0.16em] text-slate-500">Lock Deck</p>
                    <p class="mt-1 text-[11px] leading-snug text-slate-400">{{ $event->usesLockedDecks() ? 'True' : 'False' }}</p>
                </div>
            </aside>
        </div>

        @if ($event->usesSwissBracket() && $swissStandings->isNotEmpty())
            <section class="border border-cyan-400/20 bg-slate-950/58 p-2.5">
                <div class="flex items-center justify-between gap-2">
                    <h5 class="text-[12px] font-semibold uppercase tracking-[0.12em] text-cyan-100">Swiss Standings</h5>
                    <span class="text-[9px] uppercase tracking-[0.14em] text-slate-500">{{ $swissStandings->count() }} players</span>
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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($swissStandings as $row)
                                <tr class="border-b border-slate-900/80">
                                    <td class="px-2 py-1.5 font-semibold text-amber-200">{{ $row['rank'] }}</td>
                                    <td class="px-2 py-1.5 font-medium text-slate-100">{{ $row['player']->user->nickname }}</td>
                                    <td class="px-2 py-1.5 text-slate-300">{{ $row['wins'] }}-{{ $row['losses'] }}</td>
                                    <td class="px-2 py-1.5 text-slate-300">{{ $row['byes'] }}</td>
                                    <td class="px-2 py-1.5 text-slate-300">{{ number_format($row['match_points'], 1) }}</td>
                                    <td class="px-2 py-1.5 text-slate-300">{{ $row['points_diff'] }}</td>
                                    <td class="px-2 py-1.5 text-slate-300">{{ $row['battle_points'] }}</td>
                                    <td class="px-2 py-1.5 text-slate-300">{{ number_format($row['buchholz'], 1) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <div class="space-y-2">
            @if ($event->usesSwissBracket())
                <section class="border border-cyan-400/20 bg-slate-950/62 p-2.5">
                    <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2">
                        <div>
                            <h5 class="text-[12px] font-semibold uppercase tracking-[0.12em] text-cyan-100">Swiss Matches</h5>
                            <p class="mt-1 text-[10px] uppercase tracking-[0.12em] text-slate-500">Rounds display left to right.</p>
                        </div>
                        <span class="rounded border border-slate-800 bg-slate-900/70 px-2 py-1 text-[9px] uppercase tracking-[0.14em] text-slate-400">{{ $swissRoundCount }}/{{ $scheduledSwissRounds }} rounds</span>
                    </div>

                    <div class="mt-2 overflow-x-auto pb-1">
                        <div class="flex min-w-max items-start gap-3">
                            @foreach ($swissRounds as $round)
                                <section class="w-[14.25rem] shrink-0 border border-slate-800/80 bg-slate-950/82">
                                    <div class="border-b border-slate-800/80 bg-slate-900/80 px-2.5 py-1.5">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-[11px] font-semibold text-slate-100">{{ $round->label ?: 'Round '.$round->round_number }}</p>
                                            <span class="rounded border {{ $round->status === 'completed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-200' : 'border-amber-500/40 bg-amber-500/10 text-amber-200' }} px-1.5 py-0.5 text-[8px] uppercase tracking-[0.14em]">{{ $round->status }}</span>
                                        </div>
                                    </div>

                                    <div class="space-y-2 p-2">
                                        @foreach ($round->matches->sortBy('match_number')->values() as $match)
                                            @include('dashboard.partials.event-preview-match-card', [
                                                'match' => $match,
                                                'layout' => 'swiss',
                                            ])
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach

                            @if ($showSwissWaitingColumn)
                                <section class="flex min-h-[14rem] w-[14.25rem] shrink-0 flex-col border border-dashed border-slate-700/80 bg-slate-950/55">
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
                                            <p class="text-[11px] text-slate-500">The board continues here after the current gate clears.</p>
                                        </div>
                                    </div>
                                </section>
                            @elseif ($showTopCutWaitingColumn)
                                <section class="flex min-h-[14rem] w-[14.25rem] shrink-0 flex-col border border-dashed border-amber-500/45 bg-amber-500/[0.05]">
                                    <div class="border-b border-dashed border-amber-500/35 bg-slate-900/65 px-2.5 py-1.5">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-[11px] font-semibold text-amber-100">Top Cut</p>
                                            <span class="rounded border border-amber-500/35 px-1.5 py-0.5 text-[8px] uppercase tracking-[0.14em] text-amber-200">waiting</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-1 items-center justify-center p-3 text-center">
                                        <div class="space-y-1.5">
                                            <p class="text-[10px] uppercase tracking-[0.16em] text-amber-200/80">Swiss Complete</p>
                                            <p class="text-sm font-semibold leading-tight text-slate-100">{{ $missingDeckCount > 0 ? 'Deck registration is still blocking top cut generation.' : 'Waiting for top cut generation.' }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $missingDeckCount > 0 ? 'Finish deck registration, then generate elimination.' : 'The elimination bracket appears here next.' }}</p>
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
                                        if ($hasThirdPlaceBracket && $loop->last) {
                                            $sortedMatches = $sortedMatches->take(1)->values();
                                        }
                                        $hasNextRound = $roundIndex < ($eliminationRounds->count() - 1);
                                    @endphp

                                    @foreach ($sortedMatches as $match)
                                        @php
                                            $gridRowStart = $step + ($loop->index * ($step * 2));
                                            $connectorDirection = $loop->index % 2 === 0 ? 'down' : 'up';
                                        @endphp
                                        @include('dashboard.partials.event-preview-match-card', [
                                            'match' => $match,
                                            'layout' => 'bracket',
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

                        @if ($thirdPlaceMatch)
                            <div class="mt-4 border-t border-slate-800/80 pt-3">
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-amber-100">Battle for 3rd Place</p>
                                    <span class="rounded border {{ $thirdPlaceMatch->status === 'completed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-200' : 'border-amber-500/40 bg-amber-500/10 text-amber-200' }} px-1.5 py-0.5 text-[8px] uppercase tracking-[0.14em]">{{ $thirdPlaceMatch->status }}</span>
                                </div>
                                <div class="max-w-[13.25rem]">
                                    @include('dashboard.partials.event-preview-match-card', [
                                        'match' => $thirdPlaceMatch,
                                        'layout' => 'default',
                                    ])
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @elseif (! $event->usesSwissBracket())
                <div class="border border-slate-800/80 bg-slate-950/65 px-3 py-3">
                    <p class="text-sm text-slate-400">No bracket rounds generated yet. Add participants, then generate the opening round.</p>
                </div>
            @endif
        </div>
    </div>

    <aside class="min-w-0 space-y-2.5">
        <article class="border border-slate-800/80 bg-slate-950/60 p-2.5">
            <div class="flex items-center justify-between gap-2">
                <h4 class="text-[12px] font-semibold uppercase tracking-[0.12em] text-amber-100">Participants</h4>
                <span class="text-[9px] uppercase tracking-[0.14em] text-slate-500">{{ $participants->count() }} total</span>
            </div>
            <div class="mt-2 max-h-[14rem] space-y-1.5 overflow-y-auto pr-1">
                @forelse ($participants as $participant)
                    <div class="border border-slate-800/80 bg-slate-950/65 px-2.5 py-1.5 text-[11px]">
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate text-slate-100">{{ $participant->player->user->nickname }}</span>
                            @if ($participant->hasRegisteredDeck())
                                <span class="rounded border border-cyan-500/40 px-1.5 py-0.5 text-[8px] uppercase tracking-[0.14em] text-cyan-200">Deck Ready</span>
                            @endif
                        </div>
                        @if (! $participant->player->user->is_claimed)
                            <p class="mt-1 text-[10px] text-slate-500">Auto account</p>
                        @endif
                    </div>
                @empty
                    <p class="text-[11px] text-slate-500">No participants registered yet.</p>
                @endforelse
            </div>
        </article>

        <article class="border border-slate-800/80 bg-slate-950/60 p-2.5">
            <div class="flex items-center justify-between gap-2">
                <h4 class="text-[12px] font-semibold uppercase tracking-[0.12em] text-cyan-100">Final Placements</h4>
                <span class="text-[9px] uppercase tracking-[0.14em] text-slate-500">{{ $results->count() }} entries</span>
            </div>
            <div class="mt-2 space-y-1.5">
                @forelse ($results as $result)
                    <div class="flex items-center gap-2 border border-slate-800/80 bg-slate-950/65 px-2.5 py-1.5 text-[11px]">
                        <span class="inline-flex min-w-7 justify-center rounded border border-amber-500/40 px-1.5 py-0.5 font-semibold text-amber-200">#{{ $result->placement }}</span>
                        <span class="truncate text-slate-100">{{ $result->player->user->nickname }}</span>
                    </div>
                @empty
                    <p class="text-[11px] text-slate-500">{{ $event->bracket_status === 'completed' ? 'No placements were generated.' : 'Placements appear after the bracket concludes.' }}</p>
                @endforelse
            </div>
        </article>

        <article class="border border-slate-800/80 bg-slate-950/60 p-2.5">
            <div class="flex items-center justify-between gap-2">
                <h4 class="text-[12px] font-semibold uppercase tracking-[0.12em] text-fuchsia-100">Auto Awards</h4>
                <span class="text-[9px] uppercase tracking-[0.14em] text-slate-500">{{ $awards->count() }} assigned</span>
            </div>
            <div class="mt-2 space-y-1.5">
                @forelse ($awards as $eventAward)
                    <div class="border border-slate-800/80 bg-slate-950/65 px-2.5 py-1.5 text-[11px]">
                        <p class="font-semibold text-cyan-200">{{ $eventAward->award->name }}</p>
                        <p class="mt-1 text-slate-100">{{ $eventAward->player->user->nickname }}</p>
                    </div>
                @empty
                    <p class="text-[11px] text-slate-500">{{ $event->bracket_status === 'completed' ? 'No auto awards were generated.' : 'Awards appear after the event concludes.' }}</p>
                @endforelse
            </div>
        </article>
    </aside>
</div>
