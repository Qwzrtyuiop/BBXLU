<x-layouts.public :title="'Players | BBXLU'">
    <div class="mx-auto w-full max-w-6xl">
        <section class="mb-8 border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.22)_0%,rgba(2,6,23,0.94)_45%,rgba(15,23,42,0.98)_100%)] px-5 py-5 shadow-[0_18px_40px_rgba(2,6,23,0.5)] sm:px-6 sm:py-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="type-kicker text-[10px] text-cyan-300/75">Public Directory</p>
                    <h1 class="type-headline mt-1 text-2xl text-cyan-100 sm:text-3xl">Players</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-300">Bayesian-adjusted season leaderboard and full player list. Click a name to open a quick profile preview.</p>
                </div>

                <div class="grid min-w-[14rem] gap-2 sm:grid-cols-2">
                    <div class="border border-amber-300/25 bg-slate-950/50 px-3 py-2.5">
                        <p class="type-kicker text-[9px] text-amber-300/75">Total Players</p>
                        <p class="type-stat mt-1 text-lg leading-none text-amber-200">{{ $leaderboard->count() }}</p>
                    </div>
                    <div class="border border-cyan-300/25 bg-slate-950/50 px-3 py-2.5">
                        <p class="type-kicker text-[9px] text-cyan-300/75">Ranked Players</p>
                        <p class="type-stat mt-1 text-lg leading-none text-cyan-100">{{ $leaderboard->where('is_ranked', true)->count() }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="border border-slate-800/85 bg-slate-900/72 p-4 shadow-[0_18px_40px_rgba(2,6,23,0.45)] sm:p-5">
            <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-800/80 pb-3">
                <div>
                    <p class="type-kicker text-[10px] text-cyan-300/75">Season Rankings</p>
                    <h2 class="type-title mt-1 text-lg text-cyan-100">Player List</h2>
                </div>
                <span class="type-label text-[10px] text-slate-500">{{ $leaderboard->count() }} total</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400">
                            <th class="px-3 py-2">Rank</th>
                            <th class="px-3 py-2">Nickname</th>
                            <th class="px-3 py-2">
                                <span title="{{ $leaderboardScoreTooltip }}" class="cursor-help border-b border-dotted border-slate-500/60">Score</span>
                            </th>
                            <th class="px-3 py-2">Events</th>
                            <th class="px-3 py-2">Firsts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaderboard as $row)
                            @php
                                $preview = $leaderboardProfiles->get($row->player_id);
                            @endphp
                            <tr class="border-b border-slate-900 {{ ($row->is_ranked ?? false) ? '' : 'bg-slate-950/25' }}">
                                <td class="px-3 py-2 text-amber-200">
                                    @if (($row->is_ranked ?? false) && $row->rank)
                                        #{{ $row->rank }}
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if ($preview)
                                        <button
                                            type="button"
                                            data-leaderboard-profile-open
                                            data-leaderboard-profile-template-id="players-profile-template-{{ $row->player_id }}"
                                            class="group min-w-0 text-left"
                                        >
                                            <p class="truncate text-slate-100 transition group-hover:text-cyan-100">{{ $row->nickname }}</p>
                                            <p class="mt-0.5 text-[11px] text-slate-500 transition group-hover:text-slate-300">
                                                {{ ($row->is_ranked ?? false) ? 'ranked player' : 'no results yet' }}
                                                @if (property_exists($row, 'is_claimed') && ! $row->is_claimed)
                                                    - auto account
                                                @endif
                                            </p>
                                        </button>
                                    @else
                                        <div class="min-w-0">
                                            <p class="truncate text-slate-100">{{ $row->nickname }}</p>
                                            <p class="mt-0.5 text-[11px] text-slate-500">
                                                {{ ($row->is_ranked ?? false) ? 'ranked player' : 'no results yet' }}
                                                @if (property_exists($row, 'is_claimed') && ! $row->is_claimed)
                                                    - auto account
                                                @endif
                                            </p>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 font-semibold text-amber-100">{{ $row->score_display ?? $row->points }}</td>
                                <td class="px-3 py-2 text-slate-300">{{ $row->events_played }}</td>
                                <td class="px-3 py-2 text-slate-300">{{ $row->first_places }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-3 text-slate-400">No ranking data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @foreach ($leaderboard as $row)
        @php
            $preview = $leaderboardProfiles->get($row->player_id);
        @endphp
        @if ($preview)
            <template id="players-profile-template-{{ $row->player_id }}">
                @include('home.partials.leaderboard-profile-modal-body', ['preview' => $preview])
            </template>
        @endif
    @endforeach

    <div data-leaderboard-profile-modal class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/75 p-4">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto border border-cyan-400/60 bg-slate-950 p-4 shadow-[0_24px_60px_rgba(2,6,23,0.72)] sm:p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <p class="type-kicker text-xs text-cyan-300">Players</p>
                    <h3 class="type-headline mt-1 text-xl text-white sm:text-2xl">Player Preview</h3>
                </div>
                <button type="button" data-leaderboard-profile-close class="inline-flex h-9 w-9 items-center justify-center border border-slate-700 text-slate-300 transition hover:border-rose-500 hover:text-rose-200">
                    <span class="sr-only">Close</span>
                    <span class="text-lg leading-none">x</span>
                </button>
            </div>

            <div data-leaderboard-profile-body></div>
        </div>
    </div>
</x-layouts.public>
