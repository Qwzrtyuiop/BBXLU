<x-layouts.public :title="'BBX La Union'">
    <section class="mb-12">
        <div class="relative px-4 pt-10 pb-8 sm:px-6 sm:pt-12 sm:pb-10 lg:px-10">
            <div class="mx-auto max-w-4xl text-center">
                <div class="relative mx-auto mb-2 flex h-60 w-60 items-center justify-center sm:h-72 sm:w-72 lg:h-80 lg:w-80">
                    <div class="absolute inset-6 rounded-full bg-cyan-400/10 blur-2xl"></div>
                    <div class="relative flex h-48 w-48 items-center justify-center sm:h-56 sm:w-56 lg:h-64 lg:w-64">
                        <img src="{{ asset('images/lu.png') }}" alt="La Union Bladers" class="relative h-[100%] w-[100%] object-contain" />
                    </div>
                </div>
                <p class="mt-2 text-5xl font-black uppercase tracking-[0.12em] text-cyan-100 drop-shadow-[0_0_14px_rgba(34,211,238,0.6)] sm:text-6xl lg:text-7xl xl:text-8xl">
                    WELCOME TO SEASON 2
                </p>
                <p class="mx-auto mt-4 max-w-2xl text-sm font-medium text-slate-300 sm:text-base">
                    Wala parin Kaming DTI Permit.
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('home') }}#register" class="rounded-none border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-amber-200 transition hover:bg-amber-500/20">
                        Register Now
                    </a>
                    <a href="{{ route('home') }}#players" class="rounded-none border border-slate-600 bg-slate-900/75 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-200 transition hover:border-cyan-400 hover:text-cyan-200">
                        View Players
                    </a>
                </div>
            </div>

            <div class="mx-auto mt-12 max-w-5xl">
                <div class="grid gap-4 sm:grid-cols-3 sm:items-stretch sm:gap-5">
                    <article class="group flex min-h-[205px] flex-col items-center justify-center px-6 py-7 text-center transition duration-200 hover:-translate-y-1">
                        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-cyan-200">Users</h3>
                        <p class="mt-4 text-4xl font-black leading-none text-amber-200">{{ $stats['users'] }}</p>
                        <p class="mt-3 text-xs text-slate-300">Registered accounts connected to ELYU BladerHub.</p>
                        <div class="mt-5 flex w-full justify-center">
                            <div class="h-px w-16 bg-gradient-to-r from-transparent via-cyan-300/85 to-transparent shadow-[0_0_12px_rgba(34,211,238,0.75)]"></div>
                        </div>
                    </article>

                    <article class="group flex min-h-[205px] flex-col items-center justify-center px-6 py-7 text-center transition duration-200 hover:-translate-y-1">
                        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-200">Players</h3>
                        <p class="mt-4 text-4xl font-black leading-none text-amber-200">{{ $stats['players'] }}</p>
                        <p class="mt-3 text-xs text-slate-300">Bladers from La Union and challengers from other regions.</p>
                        <div class="mt-5 flex w-full justify-center">
                            <div class="h-px w-16 bg-gradient-to-r from-transparent via-emerald-300/85 to-transparent shadow-[0_0_12px_rgba(52,211,153,0.75)]"></div>
                        </div>
                    </article>

                    <article class="group flex min-h-[205px] flex-col items-center justify-center px-6 py-7 text-center transition duration-200 hover:-translate-y-1">
                        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-amber-200">Events</h3>
                        <p class="mt-4 text-4xl font-black leading-none text-amber-200">{{ $stats['events'] }}</p>
                        <p class="mt-3 text-xs text-slate-300">Tournaments and sessions recorded in this season.</p>
                        <div class="mt-5 flex w-full justify-center">
                            <div class="h-px w-16 bg-gradient-to-r from-transparent via-amber-300/90 to-transparent shadow-[0_0_12px_rgba(252,211,77,0.75)]"></div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="mx-auto mt-10 w-full max-w-6xl border-t-4 border-cyan-300/80"></div>
        </div>
    </section>

    <section id="players" class="mb-12" data-events-anchor>
        <article class="mx-auto w-full max-w-6xl bg-slate-900/70 p-6 ring-1 ring-amber-400/35 sm:p-8 lg:p-9">
                <div class="mb-6 border-b border-slate-800/80 pb-4">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Season 2 Schedule</p>
                            <h1 class="mt-1 text-2xl font-extrabold uppercase tracking-wide text-amber-100 sm:text-3xl">Events</h1>
                        </div>
                        <div class="flex items-center gap-2">
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
                    <div class="no-scrollbar edge-fade-x flex min-h-[320px] items-stretch gap-4 overflow-x-auto px-1 py-2 sm:px-2" data-events-carousel>
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
                                class="group relative w-36 min-h-[300px] shrink-0 overflow-hidden bg-slate-900/80 p-4 text-left ring-1 ring-cyan-400/35 transition duration-200 hover:-translate-y-1.5 hover:ring-amber-400/70 hover:shadow-[0_10px_30px_rgba(8,145,178,0.22)] sm:w-40"
                            >
                                <div class="flex h-full flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="inline-flex h-6 items-center bg-cyan-500/10 px-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-cyan-200">
                                                {{ $event->eventType->name }}
                                            </p>
                                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ $event->status }}</p>
                                        </div>
                                        <p class="mt-3 text-[15px] font-bold uppercase leading-tight text-slate-100">{{ $event->title }}</p>
                                    </div>

                                    <div class="mt-5 space-y-2 border-t border-slate-800 pt-3">
                                        <p class="text-xs leading-snug text-slate-400">
                                            {{ \Illuminate\Support\Str::limit($event->description ?: 'No description provided.', 56) }}
                                        </p>
                                        <div class="flex items-center justify-between text-[11px] uppercase tracking-wider text-slate-500">
                                            <span>Date</span>
                                            <span class="text-slate-200">{{ $event->date->format('d M Y') }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] uppercase tracking-wider text-slate-500">
                                            <span>Location</span>
                                            <span class="max-w-[65%] truncate text-right text-slate-200">{{ $event->location ?: 'TBD' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] uppercase tracking-wider text-slate-500">
                                            <span>Players</span>
                                            <span class="font-semibold text-amber-200">{{ $event->participants_count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400">No events available.</p>
                @endif
            </article>
    </section>

    <aside class="mb-6 mx-auto w-full max-w-6xl xl:mb-0">
        <div data-float-rail class="bg-slate-900/75 p-5 ring-1 ring-slate-700/65 xl:fixed xl:left-[calc(50%-56rem)] xl:w-80">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-200">Ongoing Tournament</h2>
            </div>
            @if ($ongoingTournament)
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-100">{{ $ongoingTournament->title }}</p>
                @if ($ongoingTournamentLink)
                    <a
                        href="{{ $ongoingTournamentLink }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-3 inline-flex rounded-none border border-emerald-500/70 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-200 transition hover:bg-emerald-500/20"
                    >
                        challonge link
                    </a>
                @else
                    <p class="mt-3 text-xs text-slate-400">No Challonge link yet.</p>
                @endif
            @else
                <p class="text-sm text-slate-400">No ongoing event</p>
            @endif
        </div>
    </aside>

    <aside class="mb-12 mx-auto w-full max-w-6xl xl:mb-0">
        <div data-float-rail data-ranking-rail class="bg-slate-900/75 p-5 ring-1 ring-slate-700/65 xl:fixed xl:left-[calc(50%+36rem)] xl:w-80">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-cyan-200">Top Bladers</h2>
                <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Top 10</p>
            </div>
            <div class="space-y-1.5">
                @forelse ($leaderboard as $row)
                    <div class="flex h-8 items-center justify-between bg-slate-950/70 px-2 py-1.5 text-xs ring-1 ring-slate-800/65">
                        <p class="font-semibold text-amber-300">#{{ $row->rank }}</p>
                        <p class="truncate px-2 text-slate-200">{{ $row->nickname }}</p>
                        <p class="font-bold text-cyan-200">{{ $row->points }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No ranking data available.</p>
                @endforelse
            </div>
        </div>
    </aside>

    <section class="mb-12 grid gap-5 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <article class="bg-slate-900/75 p-5 ring-1 ring-slate-700/65">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-bold uppercase tracking-wider text-amber-100">Latest Event</h2>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Newest Finished</p>
            </div>
            @if ($latestEvent)
                <article class="h-full bg-slate-950/75 p-5 ring-1 ring-cyan-400/40">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-cyan-200">{{ $latestEvent->eventType->name }}</span>
                        <span class="text-xs uppercase tracking-wider text-slate-400">{{ $latestEvent->status }}</span>
                    </div>
                    <p class="text-2xl font-bold uppercase tracking-wide text-white">{{ $latestEvent->title }}</p>
                    <p class="mt-2 text-sm text-slate-300">{{ $latestEvent->date->format('l, d M Y') }}</p>
                    <p class="mt-1 text-sm text-slate-400">By {{ $latestEvent->creator->nickname }}</p>
                    <p class="mt-2 text-sm text-slate-400">{{ $latestEvent->participants_count }} participants</p>
                    @if ($latestEvent->location)
                        <p class="mt-1 text-sm text-slate-300">Location: {{ $latestEvent->location }}</p>
                    @endif
                    @if ($latestEvent->description)
                        <p class="mt-3 text-sm text-slate-300">{{ $latestEvent->description }}</p>
                    @endif

                    <div class="mt-4 bg-slate-900/70 p-3 ring-1 ring-slate-800/65">
                        <div class="mb-2 flex items-center justify-between">
                            <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-200">Placements</h3>
                            <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Top 4</p>
                        </div>
                        <div class="space-y-1.5">
                            @forelse ($latestEventPlacements as $result)
                                <div class="flex h-8 items-center justify-between bg-slate-950/80 px-2 py-1.5 text-xs ring-1 ring-slate-800/65">
                                    <p class="font-semibold text-amber-300">#{{ $result->placement }}</p>
                                    <p class="truncate px-2 text-slate-200">{{ $result->player->user->nickname }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">No placements recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                </article>
            @else
                <p class="text-sm text-slate-400">No finished events found yet.</p>
            @endif
        </article>

        <article class="bg-slate-900/75 p-5 ring-1 ring-slate-700/65">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-bold uppercase tracking-wider text-fuchsia-200">Award Leaders</h2>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Top By Category</p>
            </div>
            <div class="grid gap-2.5">
                @forelse ($awardLeaders as $row)
                    <div class="relative overflow-hidden bg-gradient-to-b from-slate-950/85 to-slate-950/70 px-3 py-3 ring-1 ring-slate-800/70">
                        <div class="grid min-h-[112px] grid-cols-[minmax(0,1fr)_86px] items-center gap-3">
                            <div class="pr-1">
                                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500">{{ $row['description'] }}</p>
                                <p class="mt-1 text-base font-bold text-slate-100">{{ $row['title'] }}</p>
                                @if ($row['nickname'])
                                    <p class="mt-2 truncate text-sm font-semibold text-cyan-100">{{ $row['nickname'] }}</p>
                                @else
                                    <p class="mt-2 text-sm text-slate-400">No data yet.</p>
                                @endif
                            </div>
                            <div class="h-full border-l border-slate-800/80 pl-3 text-right">
                                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500">{{ $row['award_name'] }}</p>
                                @if ($row['nickname'])
                                    <p class="mt-2 text-2xl font-black leading-none text-amber-200">{{ $row['total'] }}</p>
                                @else
                                    <p class="mt-2 text-2xl font-black leading-none text-slate-600">--</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3 h-px w-full bg-gradient-to-r from-fuchsia-300/0 via-fuchsia-300/70 to-fuchsia-300/0"></div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No award data yet.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section id="register" class="mt-12 bg-slate-900/80 p-6 ring-1 ring-amber-400/35">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-amber-300">Register</p>
                <p class="mt-2 text-sm text-slate-300">Want to compete in upcoming events? Contact the admin to create or claim your player account.</p>
            </div>
            <a href="{{ route('login') }}" class="rounded-none border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-amber-200 hover:bg-amber-500/20">
                Go To Login
            </a>
        </div>
    </section>

    <div data-event-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
        <div class="w-full max-w-2xl border border-cyan-400/60 bg-slate-950 p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-cyan-300" data-event-modal-type></p>
                    <h3 class="mt-1 text-2xl font-extrabold uppercase tracking-wide text-white" data-event-modal-title></h3>
                </div>
                <button type="button" data-event-modal-close class="inline-flex h-9 w-9 items-center justify-center border border-slate-700 text-slate-300 transition hover:border-rose-500 hover:text-rose-200">
                    <span class="sr-only">Close</span>
                    <span class="text-lg leading-none">x</span>
                </button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="border border-slate-800 bg-slate-900/70 p-3">
                    <p class="text-xs uppercase tracking-wider text-slate-500">Date</p>
                    <p class="mt-1 text-sm text-slate-100" data-event-modal-date></p>
                </div>
                <div class="border border-slate-800 bg-slate-900/70 p-3">
                    <p class="text-xs uppercase tracking-wider text-slate-500">Status</p>
                    <p class="mt-1 text-sm text-slate-100" data-event-modal-status></p>
                </div>
                <div class="border border-slate-800 bg-slate-900/70 p-3">
                    <p class="text-xs uppercase tracking-wider text-slate-500">Location</p>
                    <p class="mt-1 text-sm text-slate-100" data-event-modal-location></p>
                </div>
                <div class="border border-slate-800 bg-slate-900/70 p-3">
                    <p class="text-xs uppercase tracking-wider text-slate-500">Participants</p>
                    <p class="mt-1 text-sm text-slate-100" data-event-modal-participants></p>
                </div>
            </div>

            <div class="mt-3 border border-slate-800 bg-slate-900/70 p-3">
                <p class="text-xs uppercase tracking-wider text-slate-500">Created By</p>
                <p class="mt-1 text-sm text-slate-100" data-event-modal-created-by></p>
            </div>

            <div class="mt-3 border border-slate-800 bg-slate-900/70 p-3">
                <p class="text-xs uppercase tracking-wider text-slate-500">Description</p>
                <p class="mt-1 text-sm text-slate-200" data-event-modal-description></p>
            </div>
        </div>
    </div>
</x-layouts.public>
