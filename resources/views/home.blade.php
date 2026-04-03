<x-layouts.public :title="'BBX La Union'" :fullBleed="true">
    <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12">
    <section data-intro-half class="mb-10 flex min-h-[calc(100svh-8.8rem)] items-start sm:mb-12">
        <div data-intro-fit class="relative w-full px-4 py-3 sm:px-6 sm:py-4 lg:px-10">
            <div class="mx-auto max-w-4xl text-center">
                <div class="relative mx-auto mb-2 flex h-[clamp(7rem,18vh,16rem)] w-[clamp(7rem,18vh,16rem)] items-center justify-center">
                    <div class="absolute inset-6 rounded-full bg-cyan-400/10 blur-2xl"></div>
                    <div class="relative flex h-[clamp(5.8rem,14.5vh,13rem)] w-[clamp(5.8rem,14.5vh,13rem)] items-center justify-center">
                        <img src="{{ asset('images/lu.png') }}" alt="La Union Bladers" class="relative h-[100%] w-[100%] object-contain" />
                    </div>
                </div>
                <p class="type-headline mt-2 text-[clamp(1.9rem,7vw,4.4rem)] leading-[0.95] text-cyan-100 drop-shadow-[0_0_14px_rgba(34,211,238,0.6)] sm:tracking-[0.12em]">
                    PALDO SA NORTE SEASON 2
                </p>
                <p class="type-body mx-auto mt-4 max-w-2xl px-2 text-sm text-slate-300 sm:text-base">
                    Wala parin Kaming DTI Permit.
                </p>
            </div>

            <div class="mx-auto mt-5 max-w-5xl sm:mt-6">
                <div class="grid gap-4 sm:grid-cols-2 sm:items-stretch sm:gap-5 xl:grid-cols-3">
                    <article class="group flex min-h-[150px] flex-col items-center justify-center px-5 py-5 text-center transition duration-200 hover:-translate-y-1 sm:min-h-[168px]">
                        <h3 class="type-kicker text-sm text-cyan-200">Users</h3>
                        <p class="type-stat mt-4 text-4xl leading-none text-amber-200">{{ $stats['users'] }}</p>
                        <p class="type-body mt-3 text-xs text-slate-300">Registered accounts connected to ELYU BladerHub.</p>
                        <div class="mt-5 flex w-full justify-center">
                            <div class="h-px w-16 bg-gradient-to-r from-transparent via-cyan-300/85 to-transparent shadow-[0_0_12px_rgba(34,211,238,0.75)]"></div>
                        </div>
                    </article>

                    <article class="group flex min-h-[150px] flex-col items-center justify-center px-5 py-5 text-center transition duration-200 hover:-translate-y-1 sm:min-h-[168px]">
                        <h3 class="type-kicker text-sm text-emerald-200">Players</h3>
                        <p class="type-stat mt-4 text-4xl leading-none text-amber-200">{{ $stats['players'] }}</p>
                        <p class="type-body mt-3 text-xs text-slate-300">Bladers from La Union and challengers from other regions.</p>
                        <div class="mt-5 flex w-full justify-center">
                            <div class="h-px w-16 bg-gradient-to-r from-transparent via-emerald-300/85 to-transparent shadow-[0_0_12px_rgba(52,211,153,0.75)]"></div>
                        </div>
                    </article>

                    <article class="group flex min-h-[150px] flex-col items-center justify-center px-5 py-5 text-center transition duration-200 hover:-translate-y-1 sm:min-h-[168px]">
                        <h3 class="type-kicker text-sm text-amber-200">Events</h3>
                        <p class="type-stat mt-4 text-4xl leading-none text-amber-200">{{ $stats['events'] }}</p>
                        <p class="type-body mt-3 text-xs text-slate-300">Tournaments and sessions recorded in this season.</p>
                        <div class="mt-5 flex w-full justify-center">
                            <div class="h-px w-16 bg-gradient-to-r from-transparent via-amber-300/90 to-transparent shadow-[0_0_12px_rgba(252,211,77,0.75)]"></div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-center gap-2">
                <button
                    type="button"
                    data-proceed-btn
                    class="type-label inline-flex items-center justify-center border border-cyan-300/65 bg-cyan-400/10 px-8 py-2.5 text-xs text-cyan-100 transition hover:bg-cyan-400/20"
                >
                    Proceed
                </button>
                {{--
                <button
                    type="button"
                    data-skip-intro-btn
                    class="type-label inline-flex items-center justify-center border border-slate-700 bg-slate-950/70 px-4 py-2.5 text-[10px] text-slate-200 transition hover:border-amber-400 hover:text-amber-200"
                >
                    Skip Intro
                </button>
                --}}
            </div>

        </div>
    </section>

    @php
        $liveFeedEvents = ($ongoingTournaments ?? collect())->values();
        $liveFeedPreviews = collect($ongoingTournamentPreviews ?? []);
        $topLeaderboardRow = $leaderboard->firstWhere('rank', 1) ?? $leaderboard->first();
        $topPlayerPreview = $topLeaderboardRow ? $leaderboardProfiles->get($topLeaderboardRow->player_id) : null;
    @endphp

    <section id="players" class="relative mb-10 scroll-mt-24 sm:mb-12" data-second-half>
        <div class="pointer-events-none absolute -left-10 top-20 h-36 w-36 rounded-full bg-emerald-400/10 blur-3xl"></div>
        <div class="pointer-events-none absolute right-0 top-28 h-40 w-40 rounded-full bg-cyan-400/10 blur-3xl"></div>

        <div class="relative z-10 mb-5 sm:mb-6">
            <div class="max-w-5xl">
                <p class="type-kicker text-[11px] text-amber-300/80">Season 2 Homepage View</p>
                <h1 class="type-headline mt-1 text-2xl text-amber-100 sm:text-3xl">Live Games, Overview, and Rankings</h1>
            </div>
        </div>

        <div class="relative z-10 grid gap-4 xl:items-stretch xl:grid-cols-[minmax(17rem,0.74fr)_minmax(0,2.35fr)_minmax(17rem,0.82fr)]">

                <aside class="order-2 self-start xl:order-1">
                    <div class="border border-emerald-400/35 bg-[linear-gradient(160deg,rgba(6,78,59,0.32)_0%,rgba(2,6,23,0.94)_42%,rgba(2,6,23,0.99)_100%)] p-3 shadow-[0_18px_40px_rgba(2,6,23,0.45)] sm:p-4">
                        <div class="mb-4 border-b border-emerald-400/20 pb-3">
                            <p class="type-kicker text-[10px] text-emerald-300/80">Live Games</p>
                            <div class="mt-1 flex items-center justify-between gap-2">
                                <h2 class="type-title text-base text-emerald-100">{{ $liveFeedEvents->count() > 1 ? 'Active Tournaments' : 'Active Tournament' }}</h2>
                                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-300 shadow-[0_0_14px_rgba(110,231,183,0.9)]"></span>
                            </div>
                        </div>

                        @if ($liveFeedEvents->isNotEmpty())
                            <div class="space-y-3">
                                @foreach ($liveFeedEvents as $liveEvent)
                                    @php
                                        $livePreview = $liveFeedPreviews->get($liveEvent->id, []);
                                        $currentLiveRound = $livePreview['currentRound'] ?? null;
                                        $currentLiveRoundLabel = $currentLiveRound
                                            ? ($currentLiveRound->label ?: ucfirst(str_replace('_', ' ', $currentLiveRound->stage)).' Round '.$currentLiveRound->round_number)
                                            : 'Waiting for Round 1';
                                    @endphp
                                    <article class="border border-emerald-400/25 bg-slate-950/50 px-3 py-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="type-kicker text-[10px] text-emerald-300/80">Now Active</p>
                                                <p class="type-title mt-1 break-words text-sm leading-snug text-slate-100">{{ $liveEvent->title }}</p>
                                            </div>
                                            <span class="type-label inline-flex min-w-[2.9rem] items-center justify-center border border-emerald-400/25 bg-emerald-400/10 px-2 py-1 text-[9px] text-emerald-100">
                                                LIVE
                                            </span>
                                        </div>

                                        <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                                            <div class="border border-emerald-400/15 bg-slate-950/55 px-3 py-2">
                                                <p class="type-label text-[10px] text-slate-500">Date</p>
                                                <p class="type-body-strong mt-1 text-xs text-slate-100">{{ optional($liveEvent->date)->format('d M Y') ?? 'TBD' }}</p>
                                            </div>
                                            <div class="border border-emerald-400/15 bg-slate-950/55 px-3 py-2">
                                                <p class="type-label text-[10px] text-slate-500">Venue</p>
                                                <p class="type-body mt-1 break-words text-xs text-slate-300">{{ $liveEvent->location ?: 'TBD Venue' }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-3 border border-emerald-400/20 bg-slate-950/45 px-3 py-2.5">
                                            <p class="type-label text-[10px] text-emerald-200">{{ $liveEvent->bracketLabel() }}</p>
                                            <p class="type-body mt-1 text-xs text-slate-400">{{ $currentLiveRoundLabel }}</p>
                                        </div>

                                        <a href="{{ route('live.viewer.event', $liveEvent) }}" class="type-label mt-3 inline-flex w-full items-center justify-center border border-emerald-400/60 bg-emerald-400/10 px-3 py-2 text-[10px] text-emerald-100 transition hover:bg-emerald-400/18">
                                            Open Live Viewer
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="border border-slate-800/80 bg-slate-950/45 px-4 py-4">
                                <p class="type-title text-sm text-slate-100">No Ongoing Event</p>
                                <p class="type-body mt-2 text-xs text-slate-500">Set an event as active in the dashboard and it will appear here.</p>
                            </div>

                            <a href="{{ route('live.viewer') }}" class="type-label mt-3 inline-flex w-full items-center justify-center border border-slate-700 px-3 py-2 text-[10px] text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200">
                                Open Live Viewer
                            </a>
                        @endif
                    </div>
                </aside>

                <article class="order-1 xl:order-2">
                    <div class="relative h-full overflow-hidden border border-cyan-400/40 bg-[linear-gradient(160deg,rgba(8,47,73,0.3)_0%,rgba(2,6,23,0.94)_42%,rgba(2,6,23,0.99)_100%)] p-4 shadow-[0_22px_48px_rgba(2,6,23,0.58)] sm:p-5 lg:p-6">
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-300/0 via-cyan-300/80 to-cyan-300/0"></div>

                        <div class="relative z-10">
                            <div class="border-b border-slate-800/80 pb-4">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                                    <div>
                                        <p class="type-kicker text-[10px] text-cyan-300/80">Season Overview</p>
                                        <h2 class="type-headline mt-1 text-2xl text-white">Overview</h2>
                                    </div>
                                    @if (config('app.debug'))
                                        <button
                                            type="button"
                                            data-debug-return
                                            class="type-label inline-flex h-9 items-center justify-center border border-rose-400/60 bg-rose-500/10 px-3 text-[10px] text-rose-200 transition hover:bg-rose-500/20"
                                        >
                                            Back To Top
                                        </button>
                                    @endif
                                </div>

                                <div class="mt-4 grid gap-2 sm:grid-cols-3">
                                    <div class="border border-cyan-400/15 bg-slate-950/55 px-3 py-3">
                                        <p class="type-kicker text-[10px] text-cyan-300/80">Users</p>
                                        <p class="type-stat mt-2 text-3xl leading-none text-amber-200">{{ $stats['users'] }}</p>
                                        <p class="type-body mt-2 text-xs text-slate-400">Connected accounts in the hub.</p>
                                    </div>
                                    <div class="border border-emerald-400/15 bg-slate-950/55 px-3 py-3">
                                        <p class="type-kicker text-[10px] text-emerald-300/80">Players</p>
                                        <p class="type-stat mt-2 text-3xl leading-none text-amber-200">{{ $stats['players'] }}</p>
                                        <p class="type-body mt-2 text-xs text-slate-400">Bladers tracked this season.</p>
                                    </div>
                                    <div class="border border-amber-400/15 bg-slate-950/55 px-3 py-3">
                                        <p class="type-kicker text-[10px] text-amber-300/80">Events</p>
                                        <p class="type-stat mt-2 text-3xl leading-none text-amber-200">{{ $stats['events'] }}</p>
                                        <p class="type-body mt-2 text-xs text-slate-400">Sessions already on record.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                @if ($latestEvent)
                                    <div class="border border-slate-700/80 bg-slate-950/45 p-4 shadow-[0_14px_32px_rgba(2,6,23,0.34)]">
                                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                            <div class="min-w-0">
                                                <p class="type-kicker text-[10px] text-cyan-300/75">Latest Finished Event</p>
                                                <p class="type-title mt-1 truncate text-lg text-slate-100">{{ $latestEvent->title }}</p>
                                                <p class="type-body mt-2 text-sm text-slate-400">{{ \Illuminate\Support\Str::limit($latestEvent->description ?: 'Placements and event details are ready to review.', 120) }}</p>
                                            </div>
                                            <div class="grid shrink-0 grid-cols-2 gap-2 text-center lg:w-[15rem]">
                                                <div class="border border-slate-800/80 bg-slate-950/70 px-3 py-2">
                                                    <p class="type-label text-[9px] text-slate-500">Date</p>
                                                    <p class="type-body-strong mt-1 text-xs text-slate-100">{{ $latestEvent->date->format('d M Y') }}</p>
                                                </div>
                                                <div class="border border-slate-800/80 bg-slate-950/70 px-3 py-2">
                                                    <p class="type-label text-[9px] text-slate-500">Players</p>
                                                    <p class="type-stat mt-1 text-base text-amber-200">{{ $latestEvent->participants_count }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="border border-slate-700/80 bg-slate-950/45 px-4 py-4">
                                        <p class="type-title text-sm text-slate-100">No finished events yet</p>
                                        <p class="type-body mt-2 text-xs text-slate-500">Once a tournament wraps up, the recap will surface here.</p>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-5">
                                <div class="mb-3 flex items-end justify-between gap-3">
                                    <div>
                                        <p class="type-kicker text-[10px] text-slate-500">Season 2 Schedule</p>
                                        <h3 class="type-title mt-1 text-lg text-slate-100">Event Overview</h3>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <p class="type-label hidden text-[10px] text-slate-500 sm:block">Swipe or use arrows</p>
                                        <button
                                            type="button"
                                            data-carousel-prev
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-none border border-slate-700 bg-slate-950/80 text-slate-200 transition hover:border-cyan-400 hover:text-cyan-200"
                                            aria-label="Scroll events left"
                                        >
                                            <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                                                <path d="M12.5 4.5L7 10l5.5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="square"/>
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            data-carousel-next
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-none border border-slate-700 bg-slate-950/80 text-slate-200 transition hover:border-cyan-400 hover:text-cyan-200"
                                            aria-label="Scroll events right"
                                        >
                                            <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                                                <path d="M7.5 4.5L13 10l-5.5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="square"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                @if ($events->isNotEmpty())
                                    <div class="no-scrollbar edge-fade-x flex min-h-[300px] items-stretch gap-4 overflow-x-auto px-1 py-2 sm:min-h-[320px] sm:px-2" data-events-carousel>
                                        @foreach ($events as $event)
                                            <button
                                                type="button"
                                                data-event-card
                                                data-event-preview-open
                                                data-event-preview-template-id="home-event-preview-template-{{ $event->id }}"
                                                class="group relative w-[min(72vw,14rem)] min-h-[280px] shrink-0 overflow-hidden bg-slate-900/80 p-4 text-left ring-1 ring-cyan-400/35 transition duration-200 hover:-translate-y-1.5 hover:ring-amber-400/70 hover:shadow-[0_10px_30px_rgba(8,145,178,0.22)] sm:w-52 sm:min-h-[300px] lg:w-56"
                                            >
                                                <div class="flex h-full flex-col justify-between">
                                                    <div>
                                                        <div class="flex items-center justify-between gap-2">
                                                            <p class="type-kicker inline-flex h-6 items-center bg-cyan-500/10 px-2 text-[10px] text-cyan-200">
                                                                {{ $event->eventType->name }}
                                                            </p>
                                                            <p class="type-label text-[10px] text-slate-400">{{ $event->status }}</p>
                                                        </div>
                                                        <p class="type-title mt-3 text-[15px] leading-tight text-slate-100">{{ $event->title }}</p>
                                                    </div>

                                                    <div class="mt-5 space-y-2 border-t border-slate-800 pt-3">
                                                        <p class="type-body text-xs leading-snug text-slate-400">
                                                            {{ \Illuminate\Support\Str::limit($event->description ?: 'No description provided.', 72) }}
                                                        </p>
                                                        <div class="type-label flex items-center justify-between text-[11px] text-slate-500">
                                                            <span>Date</span>
                                                            <span class="type-body-strong text-slate-200">{{ $event->date->format('d M Y') }}</span>
                                                        </div>
                                                        <div class="type-label flex items-center justify-between text-[11px] text-slate-500">
                                                            <span>Location</span>
                                                            <span class="type-body-strong max-w-[65%] truncate text-right text-slate-200">{{ $event->location ?: 'TBD' }}</span>
                                                        </div>
                                                        <div class="type-label flex items-center justify-between text-[11px] text-slate-500">
                                                            <span>Players</span>
                                                            <span class="type-stat text-amber-200">{{ $event->participants_count }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="type-body text-sm text-slate-400">No events available.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>

                <aside class="order-3 self-start xl:order-3 xl:self-stretch">
                    <div class="flex flex-col gap-4 xl:h-full">
                        <div class="overflow-hidden border border-cyan-400/35 bg-[linear-gradient(165deg,rgba(8,47,73,0.34)_0%,rgba(2,6,23,0.93)_42%,rgba(2,6,23,0.99)_100%)] p-3 shadow-[0_18px_40px_rgba(2,6,23,0.5)] sm:p-4">
                            <div class="pointer-events-none h-1 w-full bg-gradient-to-r from-cyan-300/0 via-cyan-300/75 to-cyan-300/0"></div>
                            <div class="mb-4 border-b border-cyan-400/20 pb-3">
                                <p class="type-kicker text-[10px] text-cyan-300/80">Player Rankings</p>
                                <h2 class="type-title mt-1 text-base text-cyan-100">Top Bladers</h2>
                            </div>

                            <div class="space-y-1.5">
                @forelse ($leaderboard as $row)
                    @php
                        $rank = (int) $row->rank;
                        $preview = $leaderboardProfiles->get($row->player_id);
                        $tier = match ($rank) {
                            1 => 'diamond',
                            2 => 'gold',
                            3 => 'silver',
                            4 => 'bronze',
                            default => 'base',
                        };

                        $rowClass = match ($tier) {
                            'diamond' => 'flex min-h-10 items-center justify-between border border-sky-200/80 bg-[linear-gradient(90deg,rgba(56,189,248,0.34),rgba(14,116,144,0.24),rgba(2,6,23,0.9))] px-2.5 py-2 text-xs shadow-[0_0_20px_rgba(56,189,248,0.3)]',
                            'gold' => 'flex min-h-9 items-center justify-between border border-amber-300/65 bg-[linear-gradient(90deg,rgba(251,191,36,0.2),rgba(2,6,23,0.9))] px-2 py-1.5 text-xs',
                            'silver' => 'flex min-h-9 items-center justify-between border border-zinc-200/55 bg-[linear-gradient(90deg,rgba(228,228,231,0.22),rgba(161,161,170,0.14),rgba(2,6,23,0.9))] px-2 py-1.5 text-xs',
                            'bronze' => 'flex min-h-9 items-center justify-between border border-orange-300/60 bg-[linear-gradient(90deg,rgba(251,146,60,0.16),rgba(2,6,23,0.9))] px-2 py-1.5 text-xs',
                            default => 'flex h-8 items-center justify-between border border-slate-800/75 bg-slate-950/72 px-2 py-1.5 text-xs',
                        };
                        $interactiveRowClass = $preview
                            ? ' group w-full cursor-pointer text-left transition duration-200 ease-out hover:-translate-y-0.5 hover:scale-[1.01] hover:border-cyan-300/75 hover:shadow-[0_12px_28px_rgba(14,165,233,0.22)] focus:outline-none focus:ring-2 focus:ring-cyan-300/45'
                            : '';
                    @endphp

                    @if ($preview)
                        <button
                            type="button"
                            data-leaderboard-profile-open
                            data-leaderboard-profile-template-id="leaderboard-profile-template-{{ $row->player_id }}"
                            class="{{ $rowClass }}{{ $interactiveRowClass }}"
                        >
                            <p class="flex w-10 items-center gap-1 font-bold tracking-[0.03em] text-amber-300 [font-family:var(--font-display)]">
                                <span class="inline-flex h-3.5 w-3.5 items-center justify-center">
                                    @if ($rank === 1)
                                        <svg viewBox="0 0 20 20" fill="none" class="h-3.5 w-3.5 text-amber-300" aria-hidden="true">
                                            <path d="M3 15.5h14l-1.1-7-3.9 3.2L10 4.5 8 11.7 4.1 8.5 3 15.5Z" fill="currentColor"/>
                                            <path d="M6.2 17h7.6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                        </svg>
                                    @endif
                                </span>
                                <span class="tabular-nums">{{ $row->rank }}</span>
                            </p>
                            <p class="type-display-copy min-w-0 flex-1 truncate ml-1 pr-2 text-[13px] text-slate-100 transition group-hover:text-cyan-50">{{ $row->nickname }}</p>
                            <p class="font-bold tabular-nums tracking-[0.03em] text-cyan-200 [font-family:var(--font-display)]">{{ $row->points }}</p>
                        </button>
                    @else
                        <div class="{{ $rowClass }}">
                            <p class="flex w-10 items-center gap-1 font-bold tracking-[0.03em] text-amber-300 [font-family:var(--font-display)]">
                                <span class="inline-flex h-3.5 w-3.5 items-center justify-center">
                                    @if ($rank === 1)
                                        <svg viewBox="0 0 20 20" fill="none" class="h-3.5 w-3.5 text-amber-300" aria-hidden="true">
                                            <path d="M3 15.5h14l-1.1-7-3.9 3.2L10 4.5 8 11.7 4.1 8.5 3 15.5Z" fill="currentColor"/>
                                            <path d="M6.2 17h7.6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                        </svg>
                                    @endif
                                </span>
                                <span class="tabular-nums">{{ $row->rank }}</span>
                            </p>
                            <p class="type-display-copy min-w-0 flex-1 truncate ml-1 pr-2 text-[13px] text-slate-100">{{ $row->nickname }}</p>
                            <p class="font-bold tabular-nums tracking-[0.03em] text-cyan-200 [font-family:var(--font-display)]">{{ $row->points }}</p>
                        </div>
                    @endif
                @empty
                    <p class="type-body text-sm text-slate-400">No ranking data available.</p>
                @endforelse
                            </div>
                        </div>

                        <div class="overflow-hidden border border-amber-400/30 bg-[linear-gradient(160deg,rgba(251,191,36,0.08)_0%,rgba(2,6,23,0.93)_45%,rgba(2,6,23,0.99)_100%)] p-3 shadow-[0_16px_34px_rgba(2,6,23,0.42)] sm:p-4 xl:flex xl:flex-1 xl:flex-col">
                            <div class="mb-4 border-b border-amber-400/20 pb-3">
                                <p class="type-kicker text-[10px] text-amber-300/80">Top Player Stat</p>
                                <h2 class="type-title mt-1 text-base text-amber-100">{{ $topPlayerPreview['nickname'] ?? 'No Top Player Yet' }}</h2>
                            </div>

                            @if ($topPlayerPreview)
                                <div class="flex flex-col gap-2 xl:flex-1">
                                    <div class="border border-slate-800/80 bg-slate-950/55 px-3 py-3">
                                        <p class="type-label text-[9px] text-slate-500">Most Used Bey</p>
                                        <p class="type-title mt-2 text-[15px] leading-tight text-cyan-100">{{ $topPlayerPreview['most_used_bey'] ?: '-' }}</p>
                                        @if (! empty($topPlayerPreview['most_used_bey']))
                                            <p class="type-body mt-1 text-xs text-slate-400">{{ $topPlayerPreview['most_used_bey_count'] }} uses</p>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="border border-slate-800/80 bg-slate-950/55 px-3 py-3">
                                            <p class="type-label text-[9px] text-slate-500">Win Rate</p>
                                            <p class="type-stat mt-2 text-2xl leading-none text-amber-100">{{ isset($topPlayerPreview['win_rate']) ? number_format((float) $topPlayerPreview['win_rate'], 1).'%' : '-' }}</p>
                                        </div>
                                        <div class="border border-slate-800/80 bg-slate-950/55 px-3 py-3">
                                            <p class="type-label text-[9px] text-slate-500">Best Finish</p>
                                            <p class="type-title mt-2 text-[15px] leading-tight text-fuchsia-100">{{ ! empty($topPlayerPreview['best_finish']) ? \Illuminate\Support\Str::headline($topPlayerPreview['best_finish']) : '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="hidden xl:block xl:flex-1"></div>
                                    <button
                                        type="button"
                                        data-leaderboard-profile-open
                                        data-leaderboard-profile-template-id="leaderboard-profile-template-{{ $topPlayerPreview['player_id'] }}"
                                        class="type-label mt-auto inline-flex w-full items-center justify-center border border-amber-400/55 bg-amber-400/10 px-3 py-2 text-[10px] text-amber-100 transition hover:bg-amber-400/18"
                                    >
                                        Open Player Preview
                                    </button>
                                </div>
                            @else
                                <p class="type-body text-sm text-slate-400">No top player data available yet.</p>
                            @endif
                        </div>
                    </div>
                </aside>
        </div>
    </section>

    <section class="mb-10 grid gap-5 sm:mb-12 xl:items-stretch xl:grid-cols-[minmax(0,1.58fr)_minmax(19rem,0.84fr)_minmax(19rem,0.92fr)]">
        <article class="flex h-full flex-col bg-slate-900/75 p-5 ring-1 ring-slate-700/65">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="type-title text-lg text-amber-100">Latest Event</h2>
                <p class="type-kicker text-xs text-slate-500">Newest Finished</p>
            </div>
            @if ($latestEvent)
                <article class="relative flex h-full flex-col overflow-hidden bg-[linear-gradient(160deg,rgba(8,47,73,0.28)_0%,rgba(2,6,23,0.94)_40%,rgba(2,6,23,0.99)_100%)] p-5 ring-1 ring-cyan-400/45 shadow-[0_18px_44px_rgba(2,6,23,0.56)]">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-300/0 via-cyan-300/80 to-cyan-300/0"></div>
                    <div class="pointer-events-none absolute -right-10 top-8 h-28 w-28 rounded-full bg-cyan-400/10 blur-2xl"></div>
                    <div class="relative z-10 flex h-full flex-col">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                            <span class="type-kicker inline-flex h-6 items-center border border-cyan-300/40 bg-cyan-400/10 px-2 text-[10px] text-cyan-200">{{ $latestEvent->eventType->name }}</span>
                            <a
                                href="{{ route('live.viewer.event', $latestEvent) }}"
                                class="type-label inline-flex items-center justify-center border border-emerald-400/55 bg-emerald-400/10 px-3 py-2 text-[10px] text-emerald-100 transition hover:bg-emerald-400/18"
                            >
                                View Event
                            </a>
                        </div>

                        <p class="type-headline max-w-4xl text-2xl leading-tight text-white">{{ $latestEvent->title }}</p>

                        <div class="mt-4 grid gap-2 lg:grid-cols-2">
                            <div class="border border-slate-700/80 bg-slate-950/65 px-3 py-2">
                                <p class="type-label text-[10px] text-slate-500">Date</p>
                                <p class="type-body-strong mt-1 text-xs text-slate-100">{{ $latestEvent->date->format('l, d M Y') }}</p>
                            </div>
                            <div class="border border-slate-700/80 bg-slate-950/65 px-3 py-2">
                                <p class="type-label text-[10px] text-slate-500">By</p>
                                <p class="type-body-strong mt-1 truncate text-xs text-slate-100">{{ optional($latestEvent->creator)->nickname ?: 'System' }}</p>
                            </div>
                            <div class="border border-slate-700/80 bg-slate-950/65 px-3 py-2">
                                <p class="type-label text-[10px] text-slate-500">Players</p>
                                <p class="type-stat mt-1 text-xs text-amber-200">{{ $latestEvent->participants_count }}</p>
                            </div>
                            <div class="border border-slate-700/80 bg-slate-950/65 px-3 py-2">
                                <p class="type-label text-[10px] text-slate-500">Venue</p>
                                <p class="type-body-strong mt-1 text-xs text-slate-100 break-words">{{ $latestEvent->location ?: 'TBD Venue' }}</p>
                            </div>
                        </div>

                        @if ($latestEvent->description)
                            <p class="type-body mt-4 border-l-2 border-cyan-400/40 pl-3 text-sm leading-relaxed text-slate-300">{{ $latestEvent->description }}</p>
                        @endif

                        <div class="mt-5 flex flex-1 flex-col border-t border-cyan-400/25 pt-3">
                            <div class="mb-2 flex items-center justify-between">
                                <h3 class="type-kicker text-xs text-emerald-200">Placements</h3>
                                <p class="type-label text-[10px] text-slate-500">Top 4</p>
                            </div>
                            <div class="space-y-1.5">
                                @forelse ($latestEventPlacements as $result)
                                    @php
                                        $placement = (int) $result->placement;
                                        $placementTier = match ($placement) {
                                            1 => 'diamond',
                                            2 => 'gold',
                                            3 => 'silver',
                                            4 => 'bronze',
                                            default => 'base',
                                        };

                                        $placementRowClass = match ($placementTier) {
                                            'diamond' => 'flex min-h-12 items-center border border-sky-200/80 bg-[linear-gradient(90deg,rgba(56,189,248,0.34),rgba(14,116,144,0.24),rgba(2,6,23,0.9))] px-3 py-2.5 text-sm shadow-[0_0_20px_rgba(56,189,248,0.3)]',
                                            'gold' => 'flex min-h-11 items-center border border-amber-300/65 bg-[linear-gradient(90deg,rgba(251,191,36,0.2),rgba(2,6,23,0.9))] px-3 py-2 text-sm',
                                            'silver' => 'flex min-h-11 items-center border border-zinc-200/55 bg-[linear-gradient(90deg,rgba(228,228,231,0.22),rgba(161,161,170,0.14),rgba(2,6,23,0.9))] px-3 py-2 text-sm',
                                            'bronze' => 'flex min-h-11 items-center border border-orange-300/60 bg-[linear-gradient(90deg,rgba(251,146,60,0.16),rgba(2,6,23,0.9))] px-3 py-2 text-sm',
                                            default => 'flex min-h-10 items-center border border-slate-800/75 bg-slate-950/72 px-3 py-2 text-sm',
                                        };
                                    @endphp
                                    <div class="{{ $placementRowClass }}">
                                        <p class="flex w-12 items-center gap-1.5 text-sm font-bold tracking-[0.03em] text-amber-300 [font-family:var(--font-display)]">
                                            <span class="inline-flex h-4 w-4 items-center justify-center">
                                                @if ($placement === 1)
                                                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4 text-amber-300" aria-hidden="true">
                                                        <path d="M3 15.5h14l-1.1-7-3.9 3.2L10 4.5 8 11.7 4.1 8.5 3 15.5Z" fill="currentColor"/>
                                                        <path d="M6.2 17h7.6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                                    </svg>
                                                @endif
                                            </span>
                                            <span class="tabular-nums">{{ $placement }}</span>
                                        </p>
                                        <p class="type-display-copy min-w-0 flex-1 truncate pr-1 text-[15px] leading-tight text-slate-100">{{ $result->player->user->nickname }}</p>
                                    </div>
                                @empty
                                    <p class="type-body text-sm text-slate-400">No placements recorded yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </article>
            @else
                <p class="type-body text-sm text-slate-400">No finished events found yet.</p>
            @endif
        </article>

        <article class="relative flex h-full flex-col overflow-hidden bg-[linear-gradient(158deg,rgba(76,29,149,0.22)_0%,rgba(2,6,23,0.93)_40%,rgba(2,6,23,0.99)_100%)] p-5 ring-1 ring-fuchsia-400/35 shadow-[0_18px_42px_rgba(2,6,23,0.55)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-fuchsia-300/0 via-fuchsia-300/75 to-fuchsia-300/0"></div>
            <div class="pointer-events-none absolute -left-12 top-8 h-28 w-28 rounded-full bg-fuchsia-400/10 blur-2xl"></div>
            <div class="relative z-10 flex h-full flex-col">
                <div class="mb-4 flex items-center justify-between border-b border-fuchsia-400/15 pb-3">
                    <h2 class="type-title text-lg text-fuchsia-200">Award Leaders</h2>
                    <p class="type-kicker text-xs text-slate-500">Top By Category</p>
                </div>
                <div class="grid flex-1 content-start gap-3">
                    @forelse ($awardLeaders as $row)
                        @php
                            $awardPreview = ! empty($row['player_id']) ? $leaderboardProfiles->get($row['player_id']) : null;
                            $accentClass = match ($loop->index % 3) {
                                0 => 'border-cyan-300/40',
                                1 => 'border-fuchsia-300/40',
                                default => 'border-amber-300/40',
                            };

                            $accentLineClass = match ($loop->index % 3) {
                                0 => 'from-cyan-300/0 via-cyan-300/85 to-cyan-300/0',
                                1 => 'from-fuchsia-300/0 via-fuchsia-300/85 to-fuchsia-300/0',
                                default => 'from-amber-300/0 via-amber-300/85 to-amber-300/0',
                            };

                            $accentGlowClass = match ($loop->index % 3) {
                                0 => 'bg-cyan-400/10',
                                1 => 'bg-fuchsia-400/10',
                                default => 'bg-amber-400/10',
                            };

                            $accentTextClass = match ($loop->index % 3) {
                                0 => 'text-cyan-100',
                                1 => 'text-fuchsia-100',
                                default => 'text-amber-100',
                            };

                            $accentValueClass = match ($loop->index % 3) {
                                0 => 'text-cyan-200',
                                1 => 'text-fuchsia-200',
                                default => 'text-amber-200',
                            };

                            $cardClasses = 'group relative overflow-hidden border '.$accentClass.' bg-[linear-gradient(165deg,rgba(2,6,23,0.84)_0%,rgba(2,6,23,0.98)_100%)] p-3.5 transition duration-200';
                            $interactiveCardClasses = $awardPreview
                                ? ' w-full cursor-pointer text-left hover:-translate-y-0.5 hover:border-cyan-300/65 hover:shadow-[0_14px_28px_rgba(14,165,233,0.16)] focus:outline-none focus:ring-2 focus:ring-cyan-300/45'
                                : '';
                        @endphp
                        @if ($awardPreview)
                            <button
                                type="button"
                                data-leaderboard-profile-open
                                data-leaderboard-profile-template-id="leaderboard-profile-template-{{ $row['player_id'] }}"
                                class="{{ $cardClasses }}{{ $interactiveCardClasses }}"
                            >
                        @else
                            <div class="{{ $cardClasses }}">
                        @endif
                            <div class="pointer-events-none absolute -right-8 top-3 h-20 w-20 rounded-full {{ $accentGlowClass }} blur-2xl"></div>
                            <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r {{ $accentLineClass }}"></div>
                            <div class="relative grid min-h-[118px] grid-cols-[minmax(0,1fr)_5.5rem] gap-3">
                                <div class="min-w-0 py-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="type-label text-[9px] text-slate-500">{{ $row['description'] }}</p>
                                            <p class="type-title mt-1 text-[1rem] leading-tight {{ $accentTextClass }}">{{ $row['title'] }}</p>
                                        </div>
                                        <span class="type-label inline-flex items-center border border-slate-800/80 bg-slate-950/60 px-2 py-1 text-[8px] text-slate-400">
                                            {{ $row['award_name'] }}
                                        </span>
                                    </div>
                                    @if ($row['nickname'])
                                        <p class="type-display-copy mt-4 truncate text-[1rem] {{ $accentTextClass }}">{{ $row['nickname'] }}</p>
                                    @else
                                        <p class="type-body mt-4 text-sm text-slate-400">No leader recorded yet.</p>
                                    @endif
                                </div>
                                <div class="flex flex-col justify-between border border-slate-800/80 bg-slate-950/60 px-2.5 py-2 text-center">
                                    <p class="type-label text-[8px] text-slate-500">Total</p>
                                    @if ($row['nickname'])
                                        <p class="type-stat text-4xl leading-none {{ $accentValueClass }}">{{ $row['total'] }}</p>
                                    @else
                                        <p class="type-stat text-4xl leading-none text-slate-600">--</p>
                                    @endif
                                    <p class="type-label text-[8px] text-slate-500">Awards</p>
                                </div>
                            </div>
                        @if ($awardPreview)
                            </button>
                        @else
                            </div>
                        @endif
                    @empty
                        <p class="type-body text-sm text-slate-400">No award data yet.</p>
                    @endforelse
                </div>
            </div>
        </article>

        <article class="relative flex h-full flex-col overflow-hidden bg-[linear-gradient(158deg,rgba(8,47,73,0.24)_0%,rgba(2,6,23,0.93)_40%,rgba(2,6,23,0.99)_100%)] p-5 ring-1 ring-cyan-400/35 shadow-[0_18px_42px_rgba(2,6,23,0.55)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-300/0 via-cyan-300/75 to-cyan-300/0"></div>
            <div class="pointer-events-none absolute -right-10 top-8 h-28 w-28 rounded-full bg-cyan-400/10 blur-2xl"></div>
            <div class="relative z-10 flex h-full flex-col">
                <div class="mb-4 flex items-center justify-between border-b border-cyan-400/15 pb-3">
                    <h2 class="type-title text-lg text-cyan-100">Meta</h2>
                    <p class="type-kicker text-xs text-slate-500">Global Stats</p>
                </div>
                <div class="grid gap-2 sm:grid-cols-2 xl:flex-1 xl:grid-cols-4 xl:grid-rows-4 xl:auto-rows-fr">
                    <div class="flex min-h-[8.5rem] flex-col justify-between border border-fuchsia-400/25 bg-[linear-gradient(160deg,rgba(112,26,117,0.18)_0%,rgba(2,6,23,0.92)_100%)] p-3 xl:col-span-2 xl:row-span-2 xl:min-h-0">
                        <div class="flex flex-1 flex-col items-center justify-center text-center">
                            <p class="type-label text-[9px] text-slate-500">Most Used Bey</p>
                            <p class="type-headline mt-3 break-words text-3xl leading-none text-fuchsia-100 xl:text-[2.15rem]">{{ $metaStats['most_used_bey'] ?: '-' }}</p>
                        </div>
                        <div class="mt-4 border-t border-fuchsia-400/15 pt-3 text-center">
                            <p class="type-label text-[9px] text-fuchsia-200/80">Usage</p>
                            <p class="type-stat mt-2 text-3xl leading-none text-amber-100">{{ $metaStats['most_used_bey_count'] > 0 ? $metaStats['most_used_bey_count'] : '--' }}</p>
                            <p class="type-body mt-1 text-xs text-slate-400">{{ $metaStats['most_used_bey_count'] > 0 ? 'total uses' : 'No usage yet' }}</p>
                        </div>
                    </div>
                    <div class="flex min-h-[8.5rem] flex-col justify-between border border-cyan-400/25 bg-[linear-gradient(160deg,rgba(8,47,73,0.22)_0%,rgba(2,6,23,0.92)_100%)] p-3 xl:col-span-2 xl:row-span-2 xl:min-h-0">
                        <div class="flex flex-1 flex-col items-center justify-center text-center">
                            <p class="type-label text-[9px] text-slate-500">Most Common Finish</p>
                            <p class="type-headline mt-3 break-words text-3xl leading-none text-cyan-100 xl:text-[2.15rem]">{{ $metaStats['most_common_finish'] ? \Illuminate\Support\Str::headline($metaStats['most_common_finish']) : '-' }}</p>
                        </div>
                        <div class="mt-4 border-t border-cyan-400/15 pt-3 text-center">
                            <p class="type-label text-[9px] text-cyan-200/80">Occurrences</p>
                            <p class="type-stat mt-2 text-3xl leading-none text-amber-100">{{ $metaStats['most_common_finish_count'] > 0 ? $metaStats['most_common_finish_count'] : '--' }}</p>
                            <p class="type-body mt-1 text-xs text-slate-400">{{ $metaStats['most_common_finish_count'] > 0 ? 'recorded finishes' : 'No finish data yet' }}</p>
                        </div>
                    </div>
                    <div class="flex min-h-[7.25rem] flex-col items-center justify-center border border-slate-800/80 bg-slate-950/45 p-3 text-center xl:min-h-0">
                        <p class="type-label text-[9px] text-slate-500">Spin %</p>
                        <p class="type-stat mt-4 text-2xl leading-none text-slate-100">{{ $metaStats['finish_percentages']['spin'] !== null ? number_format($metaStats['finish_percentages']['spin'], 1).'%' : '-' }}</p>
                    </div>
                    <div class="flex min-h-[7.25rem] flex-col items-center justify-center border border-slate-800/80 bg-slate-950/45 p-3 text-center xl:min-h-0">
                        <p class="type-label text-[9px] text-slate-500">Burst %</p>
                        <p class="type-stat mt-4 text-2xl leading-none text-slate-100">{{ $metaStats['finish_percentages']['burst'] !== null ? number_format($metaStats['finish_percentages']['burst'], 1).'%' : '-' }}</p>
                    </div>
                    <div class="flex min-h-[7.25rem] flex-col items-center justify-center border border-slate-800/80 bg-slate-950/45 p-3 text-center xl:min-h-0">
                        <p class="type-label text-[9px] text-slate-500">Over %</p>
                        <p class="type-stat mt-4 text-2xl leading-none text-slate-100">{{ $metaStats['finish_percentages']['over'] !== null ? number_format($metaStats['finish_percentages']['over'], 1).'%' : '-' }}</p>
                    </div>
                    <div class="flex min-h-[7.25rem] flex-col items-center justify-center border border-slate-800/80 bg-slate-950/45 p-3 text-center xl:min-h-0">
                        <p class="type-label text-[9px] text-slate-500">Extreme %</p>
                        <p class="type-stat mt-4 text-2xl leading-none text-slate-100">{{ $metaStats['finish_percentages']['extreme'] !== null ? number_format($metaStats['finish_percentages']['extreme'], 1).'%' : '-' }}</p>
                    </div>
                    <div class="flex min-h-[7.75rem] flex-col justify-between border border-amber-400/25 bg-[linear-gradient(160deg,rgba(251,191,36,0.1)_0%,rgba(2,6,23,0.92)_100%)] p-3 xl:col-span-2 xl:min-h-0">
                        <div class="flex items-start justify-between gap-3">
                            <p class="type-label text-[9px] text-slate-500">X Side Win %</p>
                            <p class="type-label text-[9px] text-amber-200/80">Side X</p>
                        </div>
                        <div class="mt-3">
                            <p class="type-stat text-2xl leading-none text-amber-100">{{ $metaStats['x_side_win_rate'] !== null ? number_format($metaStats['x_side_win_rate'], 1).'%' : '-' }}</p>
                            <p class="type-body mt-2 text-xs text-slate-400">{{ $metaStats['x_side_record'] }}</p>
                        </div>
                    </div>
                    <div class="flex min-h-[7.75rem] flex-col justify-between border border-amber-400/25 bg-[linear-gradient(160deg,rgba(251,191,36,0.1)_0%,rgba(2,6,23,0.92)_100%)] p-3 xl:col-span-2 xl:min-h-0">
                        <div class="flex items-start justify-between gap-3">
                            <p class="type-label text-[9px] text-slate-500">B Side Win %</p>
                            <p class="type-label text-[9px] text-amber-200/80">Side B</p>
                        </div>
                        <div class="mt-3">
                            <p class="type-stat text-2xl leading-none text-amber-100">{{ $metaStats['b_side_win_rate'] !== null ? number_format($metaStats['b_side_win_rate'], 1).'%' : '-' }}</p>
                            <p class="type-body mt-2 text-xs text-slate-400">{{ $metaStats['b_side_record'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="bg-slate-900/80 p-6 ring-1 ring-amber-400/35">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="type-kicker text-xs text-amber-300">Register</p>
                <p class="type-body mt-2 text-sm text-slate-300">Want to compete in upcoming events? Sign in to continue.</p>
            </div>
            <a href="{{ route('login') }}" class="type-label w-full rounded-none border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-center text-xs text-amber-200 hover:bg-amber-500/20 sm:w-auto">
                Go To Login
            </a>
        </div>
    </section>

    </div>

    <div data-event-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto border border-cyan-400/60 bg-slate-950 p-4 shadow-[0_24px_60px_rgba(2,6,23,0.72)] sm:p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <p class="type-kicker text-xs text-cyan-300">Event Preview</p>
                    <h3 class="type-headline mt-1 text-xl text-white sm:text-2xl">Event Details</h3>
                </div>
                <button type="button" data-event-modal-close class="inline-flex h-9 w-9 items-center justify-center border border-slate-700 text-slate-300 transition hover:border-rose-500 hover:text-rose-200">
                    <span class="sr-only">Close</span>
                    <span class="text-lg leading-none">x</span>
                </button>
            </div>

            <div data-event-modal-body></div>
        </div>
    </div>

    @foreach ($events as $event)
        <template id="home-event-preview-template-{{ $event->id }}">
            @include('home.partials.event-card-modal-body', ['event' => $event])
        </template>
    @endforeach

    @foreach ($leaderboardProfiles as $preview)
        <template id="leaderboard-profile-template-{{ $preview['player_id'] }}">
            @include('home.partials.leaderboard-profile-modal-body', ['preview' => $preview])
        </template>
    @endforeach

    <div data-leaderboard-profile-modal class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/75 p-4">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto border border-cyan-400/60 bg-slate-950 p-4 shadow-[0_24px_60px_rgba(2,6,23,0.72)] sm:p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <p class="type-kicker text-xs text-cyan-300">Player Profile</p>
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
