@php
    $preview = $ongoingTournamentPreview ?? [];
    $participants = $preview['participants'] ?? collect();
    $results = $preview['results'] ?? collect();
    $awards = $preview['awards'] ?? collect();
    $rounds = $preview['rounds'] ?? collect();
    $swissStandings = ($preview['swissStandings'] ?? collect())->take(10);
    $pendingMatchCount = $preview['pendingMatchCount'] ?? 0;
    $completedMatchCount = $preview['completedMatchCount'] ?? 0;
    $currentRound = $preview['currentRound'] ?? null;
    $currentRoundPendingCount = $preview['currentRoundPendingCount'] ?? 0;

    $swissRounds = $rounds->where('stage', 'swiss')->sortBy('round_number')->values();
    $eliminationRounds = $ongoingTournament->usesSwissBracket()
        ? $rounds->where('stage', 'single_elim')->sortBy('round_number')->values()
        : $rounds->sortBy('round_number')->values();
    $swissRoundCount = $swissRounds->count();
    $singleElimRoundCount = $eliminationRounds->count();
    $roundCount = $rounds->count();
    $scheduledSwissRounds = $ongoingTournament->usesSwissBracket() ? max(1, (int) ($ongoingTournament->swiss_rounds ?? 1)) : 0;
    $lastSwissRound = $swissRounds->last();
    $showSwissWaitingColumn = $ongoingTournament->usesSwissBracket()
        && $ongoingTournament->bracket_status !== 'completed'
        && $singleElimRoundCount === 0
        && $swissRoundCount < $scheduledSwissRounds;
    $showTopCutWaitingColumn = $ongoingTournament->usesSwissBracket()
        && $ongoingTournament->bracket_status !== 'completed'
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
        $swissRounds->isEmpty() => 'Waiting for the opening round to be generated.',
        $lastSwissRound && $lastSwissRound->status !== 'completed' => 'Swiss is still in progress.',
        default => 'Waiting for the next round pairing.',
    };
    $phaseLabel = match (true) {
        $ongoingTournament->bracket_status === 'completed' => 'event concluded',
        $singleElimRoundCount > 0 => 'top cut live',
        $swissRoundCount > 0 => 'swiss live',
        default => 'registration',
    };
    $currentRoundLabel = $currentRound
        ? ($currentRound->label ?: ucfirst(str_replace('_', ' ', $currentRound->stage)).' Round '.$currentRound->round_number)
        : 'No active round';
    $currentRoundSubtitle = match (true) {
        ! $currentRound => 'Pairings will appear here once play starts.',
        $currentRoundPendingCount > 0 => $currentRoundPendingCount.' '.\Illuminate\Support\Str::plural('Match', $currentRoundPendingCount).' Remaining',
        $ongoingTournament->bracket_status === 'completed' => 'All matches for this event are complete.',
        default => 'Round complete. Waiting for the next stage.',
    };
    $eliminationBaseMatchCount = max(1, (int) ($eliminationRounds->first()?->matches->count() ?? 0));
    $eliminationGridRows = max(1, ($eliminationBaseMatchCount * 2) - 1);
    $eliminationRowHeightRem = 4.75;
@endphp

<section class="scroll-mt-24">
    <article class="min-h-[calc(100svh-8.75rem)] overflow-hidden bg-[linear-gradient(160deg,rgba(8,47,73,0.22)_0%,rgba(2,6,23,0.95)_38%,rgba(2,6,23,0.99)_100%)] p-3 ring-1 ring-cyan-400/35 shadow-[0_20px_48px_rgba(2,6,23,0.52)] sm:p-4 lg:p-5">
        <div class="space-y-3 xl:min-h-[calc(100svh-15.25rem)]">
            <div class="min-w-0 space-y-3">
                <section class="border border-slate-800/80 bg-slate-950/72 px-4 py-3">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap gap-2">
                                <span class="type-label border border-emerald-400/55 bg-emerald-400/10 px-2.5 py-1 text-[10px] text-emerald-100">ACTIVE</span>
                                <span class="type-label border border-cyan-400/45 bg-cyan-400/10 px-2.5 py-1 text-[10px] text-cyan-100">{{ strtoupper($phaseLabel) }}</span>
                            </div>
                            <h2 class="type-headline mt-2 break-words text-2xl leading-tight text-white sm:text-[2rem]">{{ $ongoingTournament->title }}</h2>
                            <p class="type-body mt-2 text-sm text-slate-300">{{ $ongoingTournament->date->format('D, d M Y') }} / {{ $ongoingTournament->eventType->name }} / {{ $ongoingTournament->bracketLabel() }}</p>
                            @if ($ongoingTournament->location)
                                <p class="type-body mt-1 text-sm text-slate-500">{{ $ongoingTournament->location }}</p>
                            @endif
                        </div>
                        <span class="type-label border border-cyan-400/40 px-2.5 py-1 text-[10px] text-cyan-100">{{ $currentRoundLabel }}</span>
                    </div>

                    <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-5">
                        @foreach ([
                            ['label' => 'Players', 'value' => $participants->count(), 'tone' => 'text-amber-200'],
                            ['label' => 'Rounds', 'value' => $roundCount, 'tone' => 'text-amber-200'],
                            ['label' => 'Pending', 'value' => $pendingMatchCount, 'tone' => $pendingMatchCount > 0 ? 'text-amber-200' : 'text-emerald-200'],
                            ['label' => 'Completed', 'value' => $completedMatchCount, 'tone' => 'text-cyan-200'],
                            ['label' => 'Top Cut', 'value' => $ongoingTournament->usesSwissBracket() ? ($ongoingTournament->top_cut_size ?: '-') : 'Direct', 'tone' => 'text-cyan-200'],
                        ] as $metric)
                            <div class="border border-slate-800 bg-slate-900/70 px-3 py-2">
                                <p class="type-label text-[10px] text-slate-500">{{ $metric['label'] }}</p>
                                <p class="type-headline mt-1 text-lg leading-none {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 grid gap-2 xl:grid-cols-[minmax(0,1.45fr)_minmax(0,1fr)]">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <div class="border border-slate-800/80 bg-slate-900/65 px-3 py-2">
                                <p class="type-label text-[10px] text-slate-500">Current Round</p>
                                <p class="type-title mt-1 text-sm text-slate-100">{{ $currentRoundLabel }}</p>
                            </div>
                            <div class="border border-slate-800/80 bg-slate-900/65 px-3 py-2">
                                <p class="type-label text-[10px] text-slate-500">Current State</p>
                                <p class="type-body mt-1 text-sm text-slate-300">{{ $currentRoundSubtitle }}</p>
                            </div>
                        </div>

                        <div class="grid gap-2 sm:grid-cols-2">
                            <button
                                type="button"
                                data-live-detail-open
                                data-live-detail-template-id="live-placements-template"
                                data-live-detail-title="Placements"
                                class="flex items-center justify-between gap-3 border border-slate-800/80 bg-slate-900/65 px-3 py-2.5 text-left transition hover:border-cyan-400/45 hover:bg-slate-900"
                            >
                                <p class="type-title text-sm text-cyan-100">Placements</p>
                                <div class="shrink-0 text-right">
                                    <p class="type-label text-[10px] text-slate-500">{{ $results->count() }}</p>
                                    <p class="type-label mt-1 text-[10px] text-cyan-200">OPEN</p>
                                </div>
                            </button>

                            <button
                                type="button"
                                data-live-detail-open
                                data-live-detail-template-id="live-awards-template"
                                data-live-detail-title="Awards"
                                class="flex items-center justify-between gap-3 border border-slate-800/80 bg-slate-900/65 px-3 py-2.5 text-left transition hover:border-fuchsia-400/45 hover:bg-slate-900"
                            >
                                <p class="type-title text-sm text-fuchsia-100">Awards</p>
                                <div class="shrink-0 text-right">
                                    <p class="type-label text-[10px] text-slate-500">{{ $awards->count() }}</p>
                                    <p class="type-label mt-1 text-[10px] text-fuchsia-200">OPEN</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </section>

                @if ($ongoingTournament->usesSwissBracket() && $swissStandings->isNotEmpty())
                    <section class="border border-cyan-400/20 bg-slate-950/62 p-3">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2">
                            <h4 class="type-title text-base text-cyan-100">Swiss Standings</h4>
                            <p class="type-label text-[10px] text-slate-500">Top 10</p>
                        </div>

                        <div class="mt-2 overflow-x-auto">
                            <table class="min-w-full text-left text-[11px]">
                                <thead>
                                    <tr class="border-b border-slate-800 text-slate-500">
                                        <th class="px-2 py-1.5">#</th><th class="px-2 py-1.5">Player</th><th class="px-2 py-1.5">W-L</th><th class="px-2 py-1.5">Score</th><th class="px-2 py-1.5">Diff</th><th class="px-2 py-1.5">Pts</th><th class="px-2 py-1.5">Buch.</th><th class="px-2 py-1.5">Hist</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($swissStandings as $row)
                                        <tr class="border-b border-slate-900/80">
                                            <td class="px-2 py-1.5 font-semibold text-amber-200">{{ $row['rank'] }}</td>
                                            <td class="px-2 py-1.5 font-medium text-slate-100">{{ $row['player']->user->nickname }}</td>
                                            <td class="px-2 py-1.5 text-slate-300">{{ $row['wins'] }}-{{ $row['losses'] }}</td>
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

                @if ($ongoingTournament->usesSwissBracket())
                    <section class="border border-cyan-400/20 bg-slate-950/62 p-3">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2">
                            <h4 class="type-title text-base text-cyan-100">Swiss Battles</h4>
                            <p class="type-label text-[10px] text-slate-500">{{ $swissRoundCount }}/{{ $scheduledSwissRounds }} rounds</p>
                        </div>
                        <div class="mt-3 overflow-x-auto pb-1">
                            <div class="flex min-w-max items-start gap-3">
                                @foreach ($swissRounds as $round)
                                    <section class="w-[14.25rem] shrink-0 border border-slate-800/80 bg-slate-950/82">
                                        <div class="border-b border-slate-800/80 bg-slate-900/80 px-2.5 py-1.5">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="type-title text-sm text-slate-100">{{ $round->label ?: 'Round '.$round->round_number }}</p>
                                                <span class="type-label rounded border {{ $round->status === 'completed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-200' : 'border-amber-500/40 bg-amber-500/10 text-amber-200' }} px-1.5 py-0.5 text-[8px]">{{ $round->status }}</span>
                                            </div>
                                        </div>
                                        <div class="space-y-2 p-2">
                                            @foreach ($round->matches->sortBy('match_number')->values() as $match)
                                                @include('dashboard.partials.event-preview-match-card', [
                                                    'match' => $match,
                                                    'layout' => 'swiss',
                                                    'detailUrl' => route('live.viewer.match', $match),
                                                ])
                                            @endforeach
                                        </div>
                                    </section>
                                @endforeach

                                @if ($showSwissWaitingColumn || $showTopCutWaitingColumn)
                                    <section class="flex min-h-[14rem] w-[14.25rem] shrink-0 flex-col border border-dashed {{ $showTopCutWaitingColumn ? 'border-amber-500/45 bg-amber-500/[0.05]' : 'border-slate-700/80 bg-slate-950/55' }}">
                                        <div class="border-b border-dashed {{ $showTopCutWaitingColumn ? 'border-amber-500/35' : 'border-slate-700/80' }} bg-slate-900/65 px-2.5 py-1.5">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="type-title text-sm {{ $showTopCutWaitingColumn ? 'text-amber-100' : 'text-slate-300' }}">{{ $showTopCutWaitingColumn ? 'Top Cut' : $swissWaitingTitle }}</p>
                                                <span class="type-label rounded border {{ $showTopCutWaitingColumn ? 'border-amber-500/35 text-amber-200' : 'border-slate-700/80 text-slate-500' }} px-1.5 py-0.5 text-[8px]">waiting</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-1 items-center justify-center p-3 text-center">
                                            <div class="space-y-1.5">
                                                <p class="type-title text-sm leading-tight text-slate-100">{{ $showTopCutWaitingColumn ? 'Top cut has not started yet.' : $swissWaitingDetail }}</p>
                                                <p class="type-body text-[11px] text-slate-500">{{ $showTopCutWaitingColumn ? 'The elimination bracket will appear here once qualifiers are seeded.' : 'The next Swiss column appears here after the current round closes.' }}</p>
                                            </div>
                                        </div>
                                    </section>
                                @endif
                            </div>
                        </div>
                    </section>
                @endif

                @if ($eliminationRounds->isNotEmpty())
                    <section class="border border-amber-400/20 bg-slate-950/62 p-3">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2">
                            <h4 class="type-title text-base text-amber-100">Top Cut</h4>
                            <p class="type-label text-[10px] text-slate-500">{{ $eliminationRounds->count() }} rounds</p>
                        </div>
                        <div class="mt-3 overflow-x-auto pb-1">
                            <div class="min-w-max">
                                <div class="grid gap-8" style="grid-template-columns: repeat({{ max(1, $eliminationRounds->count()) }}, minmax(0, 13.25rem));">
                                    @foreach ($eliminationRounds as $round)
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="type-title text-sm text-slate-100">{{ $round->label ?: 'Round '.$round->round_number }}</p>
                                                <span class="type-label rounded border {{ $round->status === 'completed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-200' : 'border-amber-500/40 bg-amber-500/10 text-amber-200' }} px-1.5 py-0.5 text-[8px]">{{ $round->status }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-2 grid gap-8 overflow-visible" style="grid-template-columns: repeat({{ max(1, $eliminationRounds->count()) }}, minmax(0, 13.25rem)); grid-template-rows: repeat({{ $eliminationGridRows }}, minmax(0, {{ $eliminationRowHeightRem }}rem));">
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
                                                'detailUrl' => route('live.viewer.match', $match),
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

                        @if ($thirdPlaceMatch)
                            <div class="mt-4 border-t border-slate-800/80 pt-3">
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <p class="type-title text-sm text-amber-100">Battle for 3rd Place</p>
                                    <span class="type-label rounded border {{ $thirdPlaceMatch->status === 'completed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-200' : 'border-amber-500/40 bg-amber-500/10 text-amber-200' }} px-1.5 py-0.5 text-[8px]">{{ $thirdPlaceMatch->status }}</span>
                                </div>
                                <div class="max-w-[13.25rem]">
                                    @include('dashboard.partials.event-preview-match-card', [
                                        'match' => $thirdPlaceMatch,
                                        'layout' => 'default',
                                        'detailUrl' => route('live.viewer.match', $thirdPlaceMatch),
                                    ])
                                </div>
                            </div>
                        @endif
                    </section>
                @endif
            </div>

            <aside class="hidden">
                <article class="border border-slate-800/80 bg-slate-950/60 p-3">
                    <h4 class="type-title text-base text-cyan-100">Status</h4>
                    <div class="mt-3 space-y-2">
                        <div class="border border-slate-800/80 bg-slate-900/65 px-3 py-2"><p class="type-label text-[10px] text-slate-500">Current Round</p><p class="type-title mt-1 text-sm text-slate-100">{{ $currentRoundLabel }}</p></div>
                        <div class="border border-slate-800/80 bg-slate-900/65 px-3 py-2"><p class="type-label text-[10px] text-slate-500">Current State</p><p class="type-body mt-1 text-sm text-slate-300">{{ $currentRoundSubtitle }}</p></div>
                    </div>
                </article>

                <article class="border border-slate-800/80 bg-slate-950/60 p-3 xl:flex-1">
                    <div class="space-y-2">
                        <button
                            type="button"
                            data-live-detail-open
                            data-live-detail-template-id="live-placements-template"
                            data-live-detail-title="Placements"
                            class="flex w-full items-center justify-between gap-3 border border-slate-800/80 bg-slate-900/65 px-3 py-3 text-left transition hover:border-cyan-400/45 hover:bg-slate-900"
                        >
                            <div class="min-w-0">
                                <p class="type-title text-sm text-cyan-100">Placements</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="type-label text-[10px] text-slate-500">{{ $results->count() }} entries</p>
                                <p class="type-label mt-1 text-[10px] text-cyan-200">OPEN</p>
                            </div>
                        </button>

                        <button
                            type="button"
                            data-live-detail-open
                            data-live-detail-template-id="live-awards-template"
                            data-live-detail-title="Awards"
                            class="flex w-full items-center justify-between gap-3 border border-slate-800/80 bg-slate-900/65 px-3 py-3 text-left transition hover:border-fuchsia-400/45 hover:bg-slate-900"
                        >
                            <div class="min-w-0">
                                <p class="type-title text-sm text-fuchsia-100">Awards</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="type-label text-[10px] text-slate-500">{{ $awards->count() }} assigned</p>
                                <p class="type-label mt-1 text-[10px] text-fuchsia-200">OPEN</p>
                            </div>
                        </button>
                    </div>
                </article>
            </aside>
        </div>
    </article>
</section>

<template id="live-placements-template">
    <div class="space-y-2">
        @forelse ($results as $result)
            <div class="flex items-center gap-3 border border-slate-800/80 bg-slate-950/75 px-3 py-2 text-sm">
                <span class="inline-flex min-w-9 justify-center rounded border border-amber-500/40 px-2 py-1 font-semibold text-amber-200">#{{ $result->placement }}</span>
                <span class="min-w-0 flex-1 truncate text-slate-100">{{ $result->player->user->nickname }}</span>
            </div>
        @empty
            <div class="border border-dashed border-slate-700/80 bg-slate-950/55 px-3 py-4 text-center">
                <p class="type-title text-sm text-slate-100">No placements yet.</p>
                <p class="type-body mt-2 text-sm text-slate-500">Placements appear here when the event concludes.</p>
            </div>
        @endforelse
    </div>
</template>

<template id="live-awards-template">
    <div class="space-y-2">
        @forelse ($awards as $eventAward)
            <div class="border border-slate-800/80 bg-slate-950/75 px-3 py-2">
                <p class="type-label text-[10px] text-fuchsia-200">{{ $eventAward->award->name }}</p>
                <p class="type-body mt-1 text-sm text-slate-100">{{ $eventAward->player->user->nickname }}</p>
            </div>
        @empty
            <div class="border border-dashed border-slate-700/80 bg-slate-950/55 px-3 py-4 text-center">
                <p class="type-title text-sm text-slate-100">No awards yet.</p>
                <p class="type-body mt-2 text-sm text-slate-500">Awards will show up here once the event is done.</p>
            </div>
        @endforelse
    </div>
</template>

<div class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/82 px-4 py-6" data-live-detail-modal>
    <div class="flex max-h-[88vh] w-full max-w-2xl flex-col border border-slate-700/80 bg-slate-900 shadow-[0_24px_60px_rgba(2,6,23,0.72)]">
        <div class="flex items-center justify-between gap-3 border-b border-slate-800/80 px-4 py-3">
            <h3 class="type-title text-lg text-slate-100" data-live-detail-title>Details</h3>
            <button
                type="button"
                data-live-detail-close
                class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-300 transition hover:border-slate-500 hover:text-white"
            >
                Close
            </button>
        </div>
        <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4" data-live-detail-body></div>
    </div>
</div>
