<x-layouts.public :title="'BBX La Union'">
    <div class="mx-auto w-full xl:max-w-[48rem] 2xl:max-w-[56rem] min-[1792px]:max-w-6xl">
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

            <div class="mt-5 flex justify-center">
                <button
                    type="button"
                    data-proceed-btn
                    class="type-label inline-flex items-center justify-center border border-cyan-300/65 bg-cyan-400/10 px-8 py-2.5 text-xs text-cyan-100 transition hover:bg-cyan-400/20"
                >
                    Proceed
                </button>
            </div>

            <div class="mx-auto mt-5 w-full max-w-6xl border-t-4 border-cyan-300/80"></div>
        </div>
    </section>

    <section id="players" class="mb-10 scroll-mt-24 sm:mb-12 xl:mb-6" data-events-anchor data-second-half>
        <article class="mx-auto w-full max-w-6xl bg-slate-900/70 p-4 ring-1 ring-amber-400/35 sm:p-8 lg:p-9">
                <div class="mb-6 border-b border-slate-800/80 pb-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="type-kicker text-[11px] text-slate-500">Season 2 Schedule</p>
                            <h1 class="type-headline mt-1 text-2xl text-amber-100 sm:text-3xl">Events</h1>
                        </div>
                        <div class="flex items-center gap-2">
                            @if (config('app.debug'))
                                <button
                                    type="button"
                                    data-debug-return
                                    class="type-label inline-flex h-9 items-center justify-center border border-rose-400/60 bg-rose-500/10 px-3 text-[10px] text-rose-200 transition hover:bg-rose-500/20"
                                >
                                    Debug Return
                                </button>
                            @endif
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
                </div>

                @if ($events->isNotEmpty())
                    <div class="no-scrollbar edge-fade-x flex min-h-[300px] items-stretch gap-4 overflow-x-auto px-1 py-2 sm:min-h-[320px] sm:px-2" data-events-carousel>
                        @foreach ($events as $event)
                            <button
                                type="button"
                                data-event-card
                                data-event-title="{{ $event->title }}"
                                data-event-type="{{ $event->eventType->name }}"
                                data-event-date="{{ $event->date->format('l, d M Y') }}"
                                data-event-status="{{ strtoupper($event->status) }}"
                                data-event-location="{{ $event->location ?: 'TBD' }}"
                                data-event-created-by="{{ $event->creator->nickname }}"
                                data-event-participants="{{ $event->participants_count }}"
                                data-event-description="{{ $event->description ?: 'No description provided.' }}"
                                class="group relative w-[min(68vw,10rem)] min-h-[280px] shrink-0 overflow-hidden bg-slate-900/80 p-4 text-left ring-1 ring-cyan-400/35 transition duration-200 hover:-translate-y-1.5 hover:ring-amber-400/70 hover:shadow-[0_10px_30px_rgba(8,145,178,0.22)] sm:w-40 sm:min-h-[300px]"
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
                                            {{ \Illuminate\Support\Str::limit($event->description ?: 'No description provided.', 56) }}
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
            </article>
    </section>

    <aside class="mb-5 mx-auto w-full max-w-6xl sm:mb-6 xl:mb-0">
        <div data-float-rail class="relative overflow-hidden border border-emerald-400/40 bg-[linear-gradient(160deg,rgba(6,78,59,0.28)_0%,rgba(2,6,23,0.95)_42%,rgba(2,6,23,0.99)_100%)] p-4 shadow-[0_16px_36px_rgba(2,6,23,0.5)] xl:fixed xl:left-[calc(50%-38rem)] xl:w-48 2xl:left-[calc(50%-45rem)] 2xl:w-56 min-[1792px]:left-[calc(50%-58rem)] min-[1792px]:w-80">
            <div class="relative">
                <div class="mb-3">
                    <p class="type-kicker text-[10px] text-emerald-300/75">Live Bracket Feed</p>
                    <div class="mt-1 flex items-center justify-between gap-2">
                        <h2 class="type-title text-sm text-emerald-100">Ongoing Tournament</h2>
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-300 shadow-[0_0_14px_rgba(110,231,183,0.9)]"></span>
                    </div>
                </div>

                @if ($ongoingTournament)
                    <div class="mt-1">
                        <p class="type-kicker text-[10px] text-emerald-300/75">Now Active</p>
                        <p class="type-title mt-1 text-sm leading-snug text-slate-100 break-words">{{ $ongoingTournament->title }}</p>
                        <div class="mt-3 border-t border-emerald-400/20 pt-2.5">
                            <p class="type-label text-[11px] text-slate-200">
                                {{ optional($ongoingTournament->date)->format('d M Y') ?? 'TBD' }}
                            </p>
                            <p class="type-body mt-1 text-xs leading-snug text-slate-300 break-words">
                                {{ $ongoingTournament->location ?: 'TBD Venue' }}
                            </p>
                        </div>
                    </div>

                    @if ($ongoingTournamentLink)
                        <a
                            href="{{ $ongoingTournamentLink }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="type-label mt-3 inline-flex w-full items-center justify-center bg-emerald-400/12 px-3 py-2 text-[11px] text-emerald-100 transition hover:bg-emerald-400/24"
                        >
                            Open Challonge
                        </a>
                    @else
                        <p class="type-body mt-3 text-xs text-slate-400">No Challonge link yet.</p>
                    @endif
                @else
                    <div class="mt-1">
                        <p class="type-title text-sm text-slate-100">No Ongoing Event</p>
                        <p class="type-body mt-1 text-xs text-slate-500">Set an event to upcoming and it will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </aside>

    <aside class="mb-10 mx-auto w-full max-w-6xl sm:mb-12 xl:mb-0">
        <div data-float-rail data-ranking-rail class="relative overflow-hidden border border-cyan-400/35 bg-[linear-gradient(165deg,rgba(8,47,73,0.34)_0%,rgba(2,6,23,0.93)_42%,rgba(2,6,23,0.99)_100%)] p-4 shadow-[0_16px_36px_rgba(2,6,23,0.52)] xl:fixed xl:right-0 xl:w-[15rem] 2xl:w-[19rem] min-[1792px]:w-80">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-300/0 via-cyan-300/75 to-cyan-300/0"></div>
            <div class="mb-3">
                <p class="type-kicker text-[10px] text-cyan-300/75">Season Rankings</p>
                <h2 class="type-title mt-1 text-sm text-cyan-100">Top Bladers</h2>
            </div>
            <div class="mb-3 h-px bg-gradient-to-r from-cyan-300/0 via-cyan-300/55 to-cyan-300/0"></div>
            <div class="space-y-1.5">
                @forelse ($leaderboard as $row)
                    @php
                        $rank = (int) $row->rank;
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
                    @endphp

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
                @empty
                    <p class="type-body text-sm text-slate-400">No ranking data available.</p>
                @endforelse
            </div>
        </div>
    </aside>

    <section class="mb-10 grid gap-5 sm:mb-12 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <article class="bg-slate-900/75 p-5 ring-1 ring-slate-700/65">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="type-title text-lg text-amber-100">Latest Event</h2>
                <p class="type-kicker text-xs text-slate-500">Newest Finished</p>
            </div>
            @if ($latestEvent)
                <article class="relative h-full overflow-hidden bg-[linear-gradient(160deg,rgba(8,47,73,0.28)_0%,rgba(2,6,23,0.94)_40%,rgba(2,6,23,0.99)_100%)] p-5 ring-1 ring-cyan-400/45 shadow-[0_18px_44px_rgba(2,6,23,0.56)]">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-300/0 via-cyan-300/80 to-cyan-300/0"></div>
                    <div class="pointer-events-none absolute -right-10 top-8 h-28 w-28 rounded-full bg-cyan-400/10 blur-2xl"></div>
                    <div class="relative z-10">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="type-kicker inline-flex h-6 items-center border border-cyan-300/40 bg-cyan-400/10 px-2 text-[10px] text-cyan-200">{{ $latestEvent->eventType->name }}</span>
                            <span class="type-label text-[10px] text-emerald-200">{{ $latestEvent->status }}</span>
                        </div>

                        <p class="type-headline text-2xl leading-tight text-white">{{ $latestEvent->title }}</p>

                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            <div class="border border-slate-700/80 bg-slate-950/65 px-3 py-2">
                                <p class="type-label text-[10px] text-slate-500">Date</p>
                                <p class="type-body-strong mt-1 text-xs text-slate-100">{{ $latestEvent->date->format('l, d M Y') }}</p>
                            </div>
                            <div class="border border-slate-700/80 bg-slate-950/65 px-3 py-2">
                                <p class="type-label text-[10px] text-slate-500">By</p>
                                <p class="type-body-strong mt-1 truncate text-xs text-slate-100">{{ $latestEvent->creator->nickname }}</p>
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

                        <div class="mt-5 border-t border-cyan-400/25 pt-3">
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

        <article class="relative overflow-hidden bg-[linear-gradient(158deg,rgba(76,29,149,0.22)_0%,rgba(2,6,23,0.93)_40%,rgba(2,6,23,0.99)_100%)] p-5 ring-1 ring-fuchsia-400/35 shadow-[0_18px_42px_rgba(2,6,23,0.55)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-fuchsia-300/0 via-fuchsia-300/75 to-fuchsia-300/0"></div>
            <div class="pointer-events-none absolute -left-12 top-8 h-28 w-28 rounded-full bg-fuchsia-400/10 blur-2xl"></div>
            <div class="relative z-10">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="type-title text-lg text-fuchsia-200">Award Leaders</h2>
                    <p class="type-kicker text-xs text-slate-500">Top By Category</p>
                </div>
                <div class="space-y-2.5">
                    @forelse ($awardLeaders as $row)
                        @php
                            $accentClass = match ($loop->index % 3) {
                                0 => 'border-cyan-300/35',
                                1 => 'border-fuchsia-300/35',
                                default => 'border-amber-300/35',
                            };

                            $accentLineClass = match ($loop->index % 3) {
                                0 => 'from-cyan-300/0 via-cyan-300/85 to-cyan-300/0',
                                1 => 'from-fuchsia-300/0 via-fuchsia-300/85 to-fuchsia-300/0',
                                default => 'from-amber-300/0 via-amber-300/85 to-amber-300/0',
                            };
                        @endphp
                        <div class="relative overflow-hidden border {{ $accentClass }} bg-[linear-gradient(165deg,rgba(2,6,23,0.86)_0%,rgba(2,6,23,0.98)_100%)] px-3 py-3.5">
                            <div class="grid min-h-[102px] grid-cols-[minmax(0,1fr)_86px] items-center gap-3">
                                <div class="min-w-0 pr-1">
                                    <p class="type-label text-[9px] text-slate-500">{{ $row['description'] }}</p>
                                    <p class="type-title mt-1 text-[15px] leading-tight text-slate-100">{{ $row['title'] }}</p>
                                    @if ($row['nickname'])
                                        <p class="type-display-copy mt-2 truncate text-[15px] text-cyan-100">{{ $row['nickname'] }}</p>
                                    @else
                                        <p class="type-body mt-2 text-sm text-slate-400">No data yet.</p>
                                    @endif
                                </div>
                                <div class="flex h-full flex-col items-center justify-center border-l border-slate-800/80 pl-3 text-center">
                                    <p class="type-label text-[9px] text-slate-500">{{ $row['award_name'] }}</p>
                                    @if ($row['nickname'])
                                        <p class="type-stat mt-2 text-3xl leading-none text-amber-200">{{ $row['total'] }}</p>
                                    @else
                                        <p class="type-stat mt-2 text-3xl leading-none text-slate-600">--</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3 h-px w-full bg-gradient-to-r {{ $accentLineClass }}"></div>
                        </div>
                    @empty
                        <p class="type-body text-sm text-slate-400">No award data yet.</p>
                    @endforelse
                </div>
            </div>
        </article>
    </section>

    <section id="register" class="scroll-mt-24 bg-slate-900/80 p-6 ring-1 ring-amber-400/35">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="type-kicker text-xs text-amber-300">Register</p>
                <p class="type-body mt-2 text-sm text-slate-300">Want to compete in upcoming events? Contact the admin to create or claim your player account.</p>
            </div>
            <a href="{{ route('login') }}" class="type-label w-full rounded-none border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-center text-xs text-amber-200 hover:bg-amber-500/20 sm:w-auto">
                Go To Login
            </a>
        </div>
    </section>

    </div>

    <div data-event-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto border border-cyan-400/60 bg-slate-950 p-4 sm:p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <p class="type-kicker text-xs text-cyan-300" data-event-modal-type></p>
                    <h3 class="type-headline mt-1 text-xl text-white sm:text-2xl" data-event-modal-title></h3>
                </div>
                <button type="button" data-event-modal-close class="inline-flex h-9 w-9 items-center justify-center border border-slate-700 text-slate-300 transition hover:border-rose-500 hover:text-rose-200">
                    <span class="sr-only">Close</span>
                    <span class="text-lg leading-none">x</span>
                </button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="border border-slate-800 bg-slate-900/70 p-3">
                    <p class="type-label text-xs text-slate-500">Date</p>
                    <p class="type-body-strong mt-1 text-sm text-slate-100" data-event-modal-date></p>
                </div>
                <div class="border border-slate-800 bg-slate-900/70 p-3">
                    <p class="type-label text-xs text-slate-500">Status</p>
                    <p class="type-body-strong mt-1 text-sm text-slate-100" data-event-modal-status></p>
                </div>
                <div class="border border-slate-800 bg-slate-900/70 p-3">
                    <p class="type-label text-xs text-slate-500">Location</p>
                    <p class="type-body-strong mt-1 text-sm text-slate-100" data-event-modal-location></p>
                </div>
                <div class="border border-slate-800 bg-slate-900/70 p-3">
                    <p class="type-label text-xs text-slate-500">Participants</p>
                    <p class="type-body-strong mt-1 text-sm text-slate-100" data-event-modal-participants></p>
                </div>
            </div>

            <div class="mt-3 border border-slate-800 bg-slate-900/70 p-3">
                <p class="type-label text-xs text-slate-500">Created By</p>
                <p class="type-body-strong mt-1 text-sm text-slate-100" data-event-modal-created-by></p>
            </div>

            <div class="mt-3 border border-slate-800 bg-slate-900/70 p-3">
                <p class="type-label text-xs text-slate-500">Description</p>
                <p class="type-body mt-1 text-sm text-slate-200" data-event-modal-description></p>
            </div>
        </div>
    </div>
</x-layouts.public>
