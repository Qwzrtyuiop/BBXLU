<x-layouts.app :title="'Dashboard | BBXLU'" :fullScreen="true" :hideTopSelectors="true" :hideFrameHeader="true">
    @php
        $currentFocus = $ongoingTournament;
        $currentFocusLink = $currentFocus ? route('dashboard', ['panel' => 'workspace']) : route('dashboard', ['panel' => 'events']);
        $overviewEvent = $ongoingTournament ?? $upcomingEvents->first() ?? $selectedEvent ?? $latestEvent;
        $overviewEventLink = $overviewEvent
            ? ($overviewEvent->is_active
                ? route('dashboard', ['panel' => 'workspace'])
                : route('dashboard', ['panel' => 'events', 'event' => $overviewEvent->id]))
            : route('dashboard', ['panel' => 'events']);
        $overviewEventIsToday = $overviewEvent?->date?->isToday() ?? false;
        $overviewEventLabel = $overviewEvent
            ? ($overviewEvent->is_active
                ? 'Current Event'
                : ($overviewEvent->status === 'upcoming'
                ? ($overviewEventIsToday ? 'Current Event' : 'Next Event')
                : 'Latest Event'))
            : 'Next Event';
        $overviewQueueEvents = $overviewEvent
            ? $upcomingEvents->reject(fn ($event) => $event->id === $overviewEvent->id)
            : $upcomingEvents;
        $canRegisterOverviewEvent = $overviewEvent && $overviewEvent->status === 'upcoming';
        $showRegisterModal = $activePanel === 'overview' && ($errors->has('nickname') || $errors->has('selected_nicknames') || $errors->has('selected_nicknames.*') || $errors->has('deck_name'));
        $oldSelectedNicknames = collect(old('selected_nicknames', []))
            ->map(fn ($nickname) => trim((string) $nickname))
            ->filter()
            ->unique(fn ($nickname) => \Illuminate\Support\Str::lower($nickname))
            ->values();
        $dashboardEventParameters = $selectedEvent ? ['event' => $selectedEvent->id] : [];
        $toolbarBaseClasses = 'flex w-full min-h-[3.6rem] items-start justify-between border px-2.5 py-2 text-left transition';
        $toolbarActiveClasses = 'border-cyan-300/70 bg-cyan-500/12 text-cyan-100 shadow-[0_12px_24px_rgba(34,211,238,0.16)]';
        $toolbarInactiveClasses = 'border-slate-800/90 bg-slate-950/72 text-slate-300';
    @endphp

    <div
        data-dashboard-shell
        class="mx-auto grid h-[calc(100svh-1.9rem)] max-w-[112rem] grid-rows-[auto_minmax(0,1fr)] gap-2.5 overflow-hidden"
    >
        <section class="border border-cyan-400/30 bg-[linear-gradient(145deg,rgba(8,47,73,0.95)_0%,rgba(2,6,23,0.96)_45%,rgba(17,24,39,0.98)_100%)] px-4 py-2.5 shadow-[0_18px_42px_rgba(2,6,23,0.56)] sm:px-5">
            <div class="grid gap-2.5 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-start">
                <div>
                    <p class="type-kicker text-[10px] text-cyan-300/75">BBXLU Admin</p>
                    <div class="mt-1 flex flex-wrap items-end gap-x-3 gap-y-1">
                        <h2 class="type-headline text-[1.45rem] leading-none text-amber-100 sm:text-[1.8rem]">Tournament Dashboard</h2>
                        <p class="type-body text-[13px] text-slate-300">{{ now()->format('D, d M Y') }} - {{ auth()->user()->nickname }}</p>
                    </div>

                    <div class="mt-2.5 grid gap-1.5 sm:grid-cols-2 xl:max-w-[28rem] xl:grid-cols-4">
                        <article class="border border-cyan-300/25 bg-slate-950/50 px-2.5 py-1.5">
                            <p class="type-kicker text-[9px] text-cyan-300/75">Users</p>
                            <p class="type-stat mt-1 text-sm leading-none text-amber-200">{{ $stats['users'] }}</p>
                        </article>
                        <article class="border border-emerald-300/25 bg-slate-950/50 px-2.5 py-1.5">
                            <p class="type-kicker text-[9px] text-emerald-300/75">Players</p>
                            <p class="type-stat mt-1 text-sm leading-none text-amber-200">{{ $stats['players'] }}</p>
                        </article>
                        <article class="border border-amber-300/25 bg-slate-950/50 px-2.5 py-1.5">
                            <p class="type-kicker text-[9px] text-amber-300/75">Events</p>
                            <p class="type-stat mt-1 text-sm leading-none text-amber-200">{{ $stats['events'] }}</p>
                        </article>
                        <article class="border border-fuchsia-300/25 bg-slate-950/50 px-2.5 py-1.5">
                            <p class="type-kicker text-[9px] text-fuchsia-300/75">Upcoming</p>
                            <p class="type-stat mt-1 text-sm leading-none text-amber-200">{{ $stats['upcoming'] }}</p>
                        </article>
                    </div>
                </div>

                <div class="grid gap-1.5 sm:grid-cols-2 xl:flex xl:flex-nowrap xl:items-start xl:justify-end xl:justify-self-end">
                    <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label inline-flex items-center justify-center border border-amber-400/70 bg-amber-400/12 px-2.5 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">Create Or Edit Event</a>
                    @if ($currentFocus)
                        <a href="{{ $currentFocusLink }}" class="type-label inline-flex items-center justify-center border border-slate-700 bg-slate-950/55 px-2.5 py-1.5 text-[9px] text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200">Open Current Workspace</a>
                    @endif
                    <a href="{{ route('home') }}" class="type-label inline-flex items-center justify-center border border-slate-700 bg-slate-950/55 px-2.5 py-1.5 text-[9px] text-slate-100 transition hover:border-emerald-400 hover:text-emerald-200">Preview Home</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="type-label inline-flex w-full items-center justify-center border border-rose-500/60 bg-rose-500/10 px-2.5 py-1.5 text-[9px] text-rose-200 transition hover:bg-rose-500/20">Logout</button>
                    </form>
                </div>
            </div>
        </section>

        <section class="grid min-h-0 gap-2.5 xl:grid-cols-[11.25rem_minmax(0,1fr)]">
            <aside class="grid min-h-0 grid-rows-[auto_minmax(0,1fr)_auto] gap-2.5 border border-slate-800/85 bg-[linear-gradient(160deg,rgba(2,6,23,0.96)_0%,rgba(15,23,42,0.98)_100%)] p-2.5 shadow-[0_16px_36px_rgba(2,6,23,0.34)]">
                <div>
                    <p class="type-kicker text-[10px] text-slate-500">Toolbar</p>
                </div>

                <nav class="grid content-start gap-2" aria-label="Dashboard panels">
                    <a href="{{ route('dashboard', array_merge($dashboardEventParameters, ['panel' => 'overview'])) }}" class="{{ $toolbarBaseClasses }} {{ $activePanel === 'overview' ? $toolbarActiveClasses : $toolbarInactiveClasses }}">
                        <span>
                            <span class="type-label block text-[10px]">Overview</span>
                            <span class="type-body mt-1 block text-[11px] text-slate-500">season state</span>
                        </span>
                    </a>
                    <a href="{{ route('dashboard', array_merge($dashboardEventParameters, ['panel' => 'events'])) }}" class="{{ $toolbarBaseClasses }} {{ $activePanel === 'events' ? $toolbarActiveClasses : $toolbarInactiveClasses }}">
                        <span>
                            <span class="type-label block text-[10px]">Events</span>
                            <span class="type-body mt-1 block text-[11px] text-slate-500">form and directory</span>
                        </span>
                    </a>
                    <a href="{{ route('dashboard', array_merge($dashboardEventParameters, ['panel' => 'workspace'])) }}" class="{{ $toolbarBaseClasses }} {{ $activePanel === 'workspace' ? $toolbarActiveClasses : $toolbarInactiveClasses }}">
                        <span>
                            <span class="type-label block text-[10px]">Workspace</span>
                            <span class="type-body mt-1 block text-[11px] text-slate-500">active event only</span>
                        </span>
                    </a>
                    <a href="{{ route('dashboard', array_merge($dashboardEventParameters, ['panel' => 'players'])) }}" class="{{ $toolbarBaseClasses }} {{ $activePanel === 'players' ? $toolbarActiveClasses : $toolbarInactiveClasses }}">
                        <span>
                            <span class="type-label block text-[10px]">Players</span>
                            <span class="type-body mt-1 block text-[11px] text-slate-500">leaderboard</span>
                        </span>
                    </a>
                </nav>

                <div class="grid gap-2.5">
                    <article class="border border-emerald-400/30 bg-[linear-gradient(160deg,rgba(6,78,59,0.22)_0%,rgba(2,6,23,0.92)_100%)] p-2.5">
                        <p class="type-kicker text-[10px] text-emerald-300/75">Active Event</p>
                        <p class="type-title mt-1.5 line-clamp-2 text-sm text-slate-100">{{ $currentFocus?->title ?? 'No active event' }}</p>
                        @if ($currentFocus)
                            <p class="type-label mt-1.5 truncate text-[9px] text-slate-500">{{ $currentFocus->status }} - {{ $currentFocus->date->format('d M') }}</p>
                            <a href="{{ $currentFocusLink }}" class="type-label mt-2 inline-flex w-full items-center justify-center border border-slate-700 px-2 py-1 text-[10px] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">Open</a>
                        @else
                            <p class="type-body mt-1.5 text-xs text-slate-400">Set one from the Events panel to unlock the workspace.</p>
                        @endif
                    </article>

                    <article class="border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.22)_0%,rgba(2,6,23,0.92)_100%)] p-2.5">
                        <p class="type-kicker text-[10px] text-cyan-300/75">Latest Winner</p>
                        @if ($latestChampion && $latestEvent)
                            <p class="type-title mt-1.5 line-clamp-2 text-sm text-slate-100">{{ $latestChampion->player->user->nickname }}</p>
                            <p class="type-label mt-1.5 line-clamp-2 text-[9px] text-slate-500">{{ $latestEvent->title }}</p>
                        @else
                            <p class="type-body mt-1.5 text-xs text-slate-400">Waiting for a finished event.</p>
                        @endif
                    </article>
                </div>
            </aside>

            <section class="min-w-0 min-h-0 overflow-hidden border border-slate-800/85 bg-[linear-gradient(160deg,rgba(2,6,23,0.96)_0%,rgba(15,23,42,0.98)_100%)] p-2.5 shadow-[0_16px_36px_rgba(2,6,23,0.34)] sm:p-3">
            @if ($activePanel === 'overview')
            <div class="grid h-full gap-2 xl:grid-cols-[minmax(0,1.38fr)_12.25rem]">
                <article class="grid min-h-0 gap-2">
                    <div class="border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.22)_0%,rgba(2,6,23,0.92)_100%)] p-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="type-kicker text-[10px] text-cyan-300/75">{{ $overviewEventLabel }}</p>
                                <h3 class="type-headline mt-1 text-[1.35rem] leading-tight text-cyan-100">
                                    {{ $overviewEvent?->title ?? 'No scheduled event yet' }}
                                </h3>
                                @if ($overviewEvent)
                                    <p class="type-body mt-1 text-[12px] text-slate-400">
                                        {{ $overviewEvent->date->format('D, d M Y') }}
                                        @if ($overviewEvent->eventType)
                                            - {{ $overviewEvent->eventType->name }}
                                        @endif
                                        @if ($overviewEvent->location)
                                            - {{ $overviewEvent->location }}
                                        @endif
                                    </p>
                                @endif
                            </div>

                            @if ($overviewEvent)
                                <a href="{{ $overviewEventLink }}" class="type-label border border-slate-700 px-2 py-1 text-[9px] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">
                                    Open Workspace
                                </a>
                            @endif
                        </div>

                        @if ($overviewEvent)
                            <p class="type-body mt-2 text-[13px] text-slate-300">
                                {{ \Illuminate\Support\Str::limit($overviewEvent->description ?: ($overviewEvent->location ?: 'No description available.'), 150) }}
                            </p>

                            <div class="mt-2.5 grid gap-1.5 sm:grid-cols-2 xl:grid-cols-4">
                                <div class="border border-slate-700/70 bg-slate-950/55 px-2 py-1.5">
                                    <p class="type-label text-[9px] text-slate-500">Status</p>
                                    <p class="type-body-strong mt-1 text-[13px] text-slate-100">{{ $overviewEventIsToday && $overviewEvent->status === 'upcoming' ? 'today' : $overviewEvent->status }}</p>
                                </div>
                                <div class="border border-slate-700/70 bg-slate-950/55 px-2 py-1.5">
                                    <p class="type-label text-[9px] text-slate-500">Type</p>
                                    <p class="type-body-strong mt-1 text-[13px] text-slate-100">{{ $overviewEvent->eventType->name }}</p>
                                </div>
                                <div class="border border-slate-700/70 bg-slate-950/55 px-2 py-1.5">
                                    <p class="type-label text-[9px] text-slate-500">Players</p>
                                    <p class="type-stat mt-1 text-[13px] text-amber-200">{{ $overviewEvent->participants_count ?? 0 }}</p>
                                </div>
                                <div class="border border-slate-700/70 bg-slate-950/55 px-2 py-1.5">
                                    <p class="type-label text-[9px] text-slate-500">Venue</p>
                                    <p class="type-body-strong mt-1 truncate text-[13px] text-slate-100">{{ $overviewEvent->location ?: 'TBA' }}</p>
                                </div>
                            </div>

                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                @if ($canRegisterOverviewEvent)
                                    @if ($overviewEvent->usesLockedDecks())
                                        <a href="{{ route('dashboard', ['panel' => 'workspace']) }}" class="type-label border border-amber-400/70 bg-amber-400/12 px-2.5 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                            Register Locked Decks
                                        </a>
                                    @else
                                        <button type="button" data-register-modal-open class="type-label border border-amber-400/70 bg-amber-400/12 px-2.5 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                            Register Players
                                        </button>
                                    @endif
                                @endif

                                <a href="{{ $overviewEventLink }}" class="type-label border border-slate-700 bg-slate-950/55 px-2.5 py-1.5 text-[9px] text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200">
                                    View Event Tools
                                </a>
                            </div>
                        @else
                            <p class="type-body mt-3 text-sm text-slate-400">Create an event first so the overview can track the next schedule and player registration.</p>
                            <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label mt-3 inline-flex border border-amber-400/70 bg-amber-400/12 px-2.5 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                Create Event
                            </a>
                        @endif
                    </div>

                    <div class="grid min-h-0 gap-2 xl:grid-cols-[minmax(0,.82fr)_minmax(0,1.18fr)]">
                        <article class="min-h-0 overflow-hidden border border-amber-400/30 bg-[linear-gradient(160deg,rgba(251,191,36,0.08)_0%,rgba(2,6,23,0.92)_100%)] p-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <p class="type-title text-[13px] text-amber-100">Upcoming Events</p>
                                <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label text-[9px] text-slate-300 hover:text-amber-200">Manage</a>
                            </div>
                            <div class="mt-2 space-y-1 overflow-y-auto no-scrollbar">
                                @forelse ($overviewQueueEvents->take(3) as $event)
                                    <a href="{{ route('dashboard', ['panel' => $event->is_active ? 'workspace' : 'events', 'event' => $event->id]) }}" class="block border border-slate-800/80 bg-slate-950/65 px-2.5 py-1 transition hover:border-amber-400/55">
                                        <p class="type-title truncate text-[12px] text-slate-100">{{ $event->title }}</p>
                                        <p class="type-label mt-0.5 text-[8px] text-slate-500">{{ $event->date->format('d M') }} - {{ $event->eventType->name }}</p>
                                    </a>
                                @empty
                                    <p class="type-body text-sm text-slate-400">No additional upcoming events.</p>
                                @endforelse
                            </div>
                        </article>

                        <article class="min-h-0 overflow-hidden border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.18)_0%,rgba(2,6,23,0.92)_100%)] p-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <p class="type-title text-[13px] text-cyan-100">Leaderboard Preview</p>
                                <a href="{{ route('dashboard', ['panel' => 'players']) }}" class="type-label text-[9px] text-slate-300 hover:text-cyan-200">Players</a>
                            </div>
                            <div class="mt-2 space-y-1 overflow-y-auto no-scrollbar">
                                @forelse ($leaderboard->take(4) as $row)
                                    <div class="flex items-center gap-2.5 border border-slate-800/80 bg-slate-950/65 px-2.5 py-1">
                                        <span class="type-stat w-5 text-[13px] text-amber-300">{{ $row->rank }}</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="type-display-copy truncate text-[13px] text-slate-100">{{ $row->nickname }}</p>
                                            <p class="type-label mt-0.5 text-[8px] text-slate-500">{{ $row->events_played }} events</p>
                                        </div>
                                        <span class="type-stat text-[13px] text-cyan-200">{{ $row->points }}</span>
                                    </div>
                                @empty
                                    <p class="type-body text-sm text-slate-400">No ranking data yet.</p>
                                @endforelse
                            </div>
                        </article>
                    </div>
                </article>

                <div class="grid min-h-0 gap-2">
                    <article class="border border-fuchsia-300/35 bg-[linear-gradient(160deg,rgba(112,26,117,0.16)_0%,rgba(2,6,23,0.92)_100%)] p-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <p class="type-title text-[13px] text-fuchsia-100">Award Leaders</p>
                            <span class="type-label text-[8px] text-slate-500">top 3</span>
                        </div>
                        <div class="mt-2 space-y-1">
                            @forelse ($awardLeaders->take(3) as $row)
                                <div class="border border-slate-800/80 bg-slate-950/65 px-2.5 py-1">
                                    <p class="type-label text-[8px] text-slate-500">{{ $row['award_name'] }}</p>
                                    <div class="mt-0.5 flex items-center justify-between gap-2">
                                        <p class="type-display-copy truncate text-[13px] text-slate-100">{{ $row['nickname'] ?: 'No data yet' }}</p>
                                        <span class="type-label text-[8px] text-fuchsia-200">{{ $row['total'] }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="type-body text-sm text-slate-400">No award data yet.</p>
                            @endforelse
                        </div>
                    </article>

                    <article class="min-h-0 overflow-hidden border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.18)_0%,rgba(2,6,23,0.92)_100%)] p-2.5">
                        <p class="type-title text-[13px] text-cyan-100">{{ $latestEvent?->title ?: 'Latest Event' }}</p>
                        <div class="mt-2 space-y-1 overflow-y-auto no-scrollbar">
                            @forelse ($latestEventPlacements as $result)
                                <div class="flex items-center gap-2.5 border border-slate-800/80 bg-slate-950/65 px-2.5 py-1">
                                    <span class="type-stat w-5 text-[13px] text-amber-300">{{ $result->placement }}</span>
                                    <span class="type-display-copy min-w-0 flex-1 truncate text-[13px] text-slate-100">{{ $result->player->user->nickname }}</span>
                                </div>
                            @empty
                                <p class="type-body text-sm text-slate-400">No placements recorded yet.</p>
                            @endforelse
                        </div>
                    </article>
                </div>
            </div>

            @if ($canRegisterOverviewEvent && ! $overviewEvent->usesLockedDecks())
                <div
                    data-register-modal
                    data-register-open-on-load="{{ $showRegisterModal ? 'true' : 'false' }}"
                    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/86 px-4 py-6"
                >
                    <div class="w-full max-w-3xl border border-cyan-400/35 bg-[linear-gradient(160deg,rgba(8,47,73,0.22)_0%,rgba(2,6,23,0.98)_100%)] p-4 shadow-[0_28px_72px_rgba(2,6,23,0.72)]">
                        <div class="flex items-start justify-between gap-3 border-b border-slate-800/80 pb-3">
                            <div class="min-w-0">
                                <p class="type-kicker text-[10px] text-cyan-300/75">Register Players</p>
                                <h3 class="type-headline mt-1 text-lg text-cyan-100">{{ $overviewEvent->title }}</h3>
                                <p class="type-body mt-1 text-[12px] text-slate-400">Select registered users or add a new nickname, then confirm the list below.</p>
                            </div>

                            <button type="button" data-register-modal-close class="type-label border border-slate-700 px-2.5 py-1 text-[9px] text-slate-100 transition hover:border-rose-400 hover:text-rose-200">
                                Close
                            </button>
                        </div>

                        <form action="{{ route('events.participants.store', $overviewEvent) }}" method="POST" data-register-form class="mt-3 grid gap-3">
                            @csrf
                            <input type="hidden" name="dashboard_redirect" value="1">
                            <input type="hidden" name="dashboard_panel" value="overview">
                            <input type="hidden" name="dashboard_event_id" value="{{ $overviewEvent->id }}">

                            <div class="grid gap-3 lg:grid-cols-[minmax(0,1.08fr)_minmax(0,.92fr)]">
                                <div class="grid gap-3">
                                    <section class="border border-slate-800/80 bg-slate-950/55 p-3">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="type-title text-[13px] text-amber-100">Registered Users</p>
                                            <span class="type-label text-[8px] text-slate-500">{{ $registerableUsers->count() }} available</span>
                                        </div>

                                        <label class="mt-2 grid gap-1">
                                            <span class="text-xs text-slate-400">Select players</span>
                                            <select data-register-existing multiple size="7" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                                @forelse ($registerableUsers as $user)
                                                    <option value="{{ $user->nickname }}">
                                                        {{ $user->nickname }}@if (! $user->is_claimed) - auto account @endif
                                                    </option>
                                                @empty
                                                    <option value="" disabled>No registered users yet</option>
                                                @endforelse
                                            </select>
                                        </label>

                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <button type="button" data-register-existing-add class="type-label border border-amber-400/70 bg-amber-400/12 px-2.5 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                                Add Selected
                                            </button>
                                            <p data-register-feedback class="text-[11px] text-slate-500">Choose one or more registered users.</p>
                                        </div>
                                    </section>

                                    <section class="border border-slate-800/80 bg-slate-950/55 p-3">
                                        <p class="type-title text-[13px] text-cyan-100">Add New User</p>
                                        <label class="mt-2 grid gap-1">
                                            <span class="text-xs text-slate-400">Nickname</span>
                                            <input type="text" value="" data-register-new maxlength="255" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                        </label>

                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <button type="button" data-register-new-add class="type-label border border-cyan-400/70 bg-cyan-400/10 px-2.5 py-1.5 text-[9px] text-cyan-100 transition hover:bg-cyan-400/18">
                                                Add New Nickname
                                            </button>
                                            <p class="text-[11px] text-slate-500">New nicknames auto-create a user profile and player record.</p>
                                        </div>
                                    </section>
                                </div>

                                <section class="border border-slate-800/80 bg-slate-950/55 p-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="type-title text-[13px] text-fuchsia-100">Selected Players</p>
                                        <span data-register-count class="type-label text-[8px] text-slate-500">{{ $oldSelectedNicknames->count() }} selected</span>
                                    </div>

                                    <div data-register-selected class="mt-2 min-h-[11rem] max-h-[15rem] space-y-1.5 overflow-y-auto no-scrollbar border border-dashed border-slate-800 bg-slate-950/40 p-2"></div>
                                </section>
                            </div>

                            @if ($errors->has('nickname') || $errors->has('selected_nicknames') || $errors->has('selected_nicknames.*'))
                                <p class="text-sm text-rose-300">
                                    {{ $errors->first('nickname') ?: $errors->first('selected_nicknames') ?: $errors->first('selected_nicknames.*') }}
                                </p>
                            @endif

                            <div data-register-hidden-inputs>
                                @foreach ($oldSelectedNicknames as $nickname)
                                    <input type="hidden" name="selected_nicknames[]" value="{{ $nickname }}">
                                @endforeach
                            </div>

                            <div class="flex items-center justify-end gap-2 border-t border-slate-800/80 pt-3">
                                <button type="button" data-register-modal-close class="type-label border border-slate-700 px-3 py-1.5 text-[9px] text-slate-100 transition hover:border-slate-500 hover:text-white">
                                    Cancel
                                </button>
                                <button type="submit" data-register-submit @disabled($oldSelectedNicknames->isEmpty()) class="type-label border border-amber-400/70 bg-amber-400/12 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20 disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                                    Confirm Players
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @elseif ($activePanel === 'events')
            <div class="grid h-full gap-3 xl:grid-cols-[minmax(0,.94fr)_minmax(22rem,1.06fr)]">
                <article class="min-h-0 overflow-y-auto no-scrollbar border border-amber-400/30 bg-[linear-gradient(160deg,rgba(251,191,36,0.08)_0%,rgba(2,6,23,0.92)_100%)] p-4">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="type-kicker text-[10px] text-amber-300/75">{{ $selectedEvent ? 'Edit Mode' : 'Create Mode' }}</p>
                            <h3 class="type-headline mt-1 text-xl text-amber-100">{{ $selectedEvent ? 'Edit Event' : 'Create Event' }}</h3>
                            <p class="type-body mt-1 text-xs text-slate-400">
                                {{ $selectedEvent ? 'Updating the selected event from the directory.' : 'Fill out the form below to create a new event.' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($selectedEvent?->is_active)
                                <span class="type-label border border-emerald-400/60 bg-emerald-500/10 px-2.5 py-1 text-[10px] text-emerald-100">Active Event</span>
                            @elseif ($selectedEvent && $selectedEvent->status === 'upcoming')
                                <form action="{{ route('events.activate', $selectedEvent) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="dashboard_redirect" value="1">
                                    <input type="hidden" name="dashboard_panel" value="events">
                                    <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                                    <button class="type-label border border-emerald-500/60 bg-emerald-500/10 px-2.5 py-1 text-[10px] text-emerald-100 transition hover:bg-emerald-500/20">Set Active</button>
                                </form>
                            @endif
                            <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">
                                {{ $selectedEvent ? 'Create New Event' : 'Clear Form' }}
                            </a>
                        </div>
                    </div>

                    <form action="{{ $selectedEvent ? route('events.update', $selectedEvent) : route('events.store') }}" method="POST" class="mt-4 grid gap-3">
                        @csrf
                        @if ($selectedEvent)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="dashboard_redirect" value="1">
                        <input type="hidden" name="dashboard_panel" value="events">
                        @if ($selectedEvent)
                            <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                        @endif

                        <div class="border border-slate-800/80 bg-slate-950/55 px-3 py-2.5">
                            <p class="type-label text-[10px] text-slate-500">Form State</p>
                            @if ($selectedEvent)
                                <p class="mt-1 text-sm text-slate-100">Editing: {{ $selectedEvent->title }}</p>
                                <p class="mt-1 text-xs text-slate-400">Use Create New Event to clear the form and start a fresh entry.</p>
                            @else
                                <p class="mt-1 text-sm text-slate-100">Ready to create a new event.</p>
                                <p class="mt-1 text-xs text-slate-400">Select an existing event from the directory only if you want to load it into edit mode.</p>
                            @endif
                        </div>

                        <label class="grid gap-1">
                            <span class="text-sm text-slate-300">Title</span>
                            <input name="title" value="{{ old('title', $selectedEvent?->title) }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                        </label>

                        <label class="grid gap-1">
                            <span class="text-sm text-slate-300">Description</span>
                            <textarea name="description" rows="3" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">{{ old('description', $selectedEvent?->description) }}</textarea>
                        </label>

                        <div class="grid gap-3 sm:grid-cols-2">
                        <label class="grid gap-1">
                            <span class="text-sm text-slate-300">Bracket Type</span>
                            <select name="bracket_type" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                                    <option value="single_elim" @selected(old('bracket_type', $selectedEvent?->bracket_type ?? 'single_elim') === 'single_elim')>Single Elimination</option>
                                    <option value="swiss_single_elim" @selected(old('bracket_type', $selectedEvent?->bracket_type) === 'swiss_single_elim')>Swiss + Single Elimination</option>
                                </select>
                            </label>

                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Best Of</span>
                                <input value="7" disabled class="rounded-lg border border-slate-800 bg-slate-900/80 px-3 py-2 text-slate-400">
                            </label>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Swiss Rounds</span>
                                <input type="number" min="1" max="12" name="swiss_rounds" value="{{ old('swiss_rounds', $selectedEvent?->swiss_rounds ?: 5) }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                            </label>

                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Top Cut Size</span>
                                <select name="top_cut_size" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                                    @foreach ([2, 4, 8, 16, 32, 64] as $size)
                                        <option value="{{ $size }}" @selected((string) old('top_cut_size', $selectedEvent?->top_cut_size ?: 8) === (string) $size)>Top {{ $size }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <p class="text-xs text-slate-500">Swiss settings are only used when the event runs Swiss into a top cut.</p>

                        <label class="grid gap-2 border border-slate-800/80 bg-slate-950/55 px-3 py-3">
                            <span class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    name="is_lock_deck"
                                    value="1"
                                    @checked((bool) old('is_lock_deck', $selectedEvent?->is_lock_deck))
                                    class="mt-0.5 h-4 w-4 rounded border-slate-700 bg-slate-950 text-amber-400 focus:ring-amber-400"
                                >
                                <span>
                                    <span class="block text-sm text-slate-100">Lock deck from registration</span>
                                    <span class="mt-1 block text-xs text-slate-500">When enabled, every player must register a deck name plus Beys 1-3 before round 1. When disabled, deck registration is only required before players enter single elimination.</span>
                                </span>
                            </span>
                        </label>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Event Type</span>
                                <select name="event_type_id" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                                    @foreach ($eventTypes as $type)
                                        <option value="{{ $type->id }}" @selected((string) old('event_type_id', $selectedEvent?->event_type_id) === (string) $type->id)>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Date</span>
                                <input type="date" name="date" value="{{ old('date', optional($selectedEvent?->date)->format('Y-m-d')) }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                            </label>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Location</span>
                                <input name="location" value="{{ old('location', $selectedEvent?->location) }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                            </label>

                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Status</span>
                                <select name="status" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                                    <option value="upcoming" @selected(old('status', $selectedEvent?->status ?? 'upcoming') === 'upcoming')>Upcoming</option>
                                    <option value="finished" @selected(old('status', $selectedEvent?->status) === 'finished')>Finished</option>
                                </select>
                            </label>
                        </div>

                        <label class="grid gap-1">
                            <span class="text-sm text-slate-300">Created By (nickname)</span>
                            <input name="created_by_nickname" value="{{ old('created_by_nickname', $selectedEvent?->creator?->nickname ?? auth()->user()->nickname) }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                        </label>

                        <div class="flex flex-wrap gap-2 pt-1">
                            <button class="type-label border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-[10px] text-amber-100 transition hover:bg-amber-500/20">
                                {{ $selectedEvent ? 'Update Event' : 'Create Event' }}
                            </button>
                            <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label border border-slate-700 px-4 py-2 text-[10px] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">
                                Create New Event
                            </a>
                        </div>
                    </form>
                </article>

                <article class="min-h-0 overflow-y-auto no-scrollbar border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.18)_0%,rgba(2,6,23,0.92)_100%)] p-4">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="type-kicker text-[10px] text-cyan-300/75">Event Directory</p>
                            <h3 class="type-headline mt-1 text-xl text-cyan-100">All Events</h3>
                        </div>
                        <span class="type-label text-[10px] text-slate-500">{{ $adminEvents->count() }} total</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($adminEvents as $event)
                            <article class="border {{ $selectedEvent && $selectedEvent->id === $event->id ? 'border-amber-400/60' : ($event->is_active ? 'border-emerald-400/50' : 'border-slate-800/80') }} bg-slate-950/65 p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="type-label text-[10px] text-slate-500">{{ $event->status }}</p>
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        @if ($event->is_lock_deck)
                                            <span class="type-label border border-amber-400/60 bg-amber-500/10 px-2 py-0.5 text-[9px] text-amber-100">Lock Deck</span>
                                        @endif
                                        @if ($event->is_active)
                                            <span class="type-label border border-emerald-400/60 bg-emerald-500/10 px-2 py-0.5 text-[9px] text-emerald-100">Active</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="type-title mt-1 break-words text-sm text-slate-100">{{ $event->title }}</p>
                                <p class="type-label mt-1 text-[9px] text-slate-500">{{ $event->date->format('d M Y') }} - {{ $event->eventType->name }} - {{ $event->participants_count }} players</p>
                                <p class="type-body mt-2 text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($event->description ?: ($event->location ?: 'No description.'), 88) }}</p>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('dashboard', ['panel' => 'events', 'event' => $event->id]) }}" class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200">Edit Here</a>
                                    @if ($event->is_active)
                                        <a href="{{ route('dashboard', ['panel' => 'workspace']) }}" class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">Workspace</a>
                                    @elseif ($event->status === 'upcoming')
                                        <form action="{{ route('events.activate', $event) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="dashboard_redirect" value="1">
                                            <input type="hidden" name="dashboard_panel" value="events">
                                            <input type="hidden" name="dashboard_event_id" value="{{ $event->id }}">
                                            <button class="type-label border border-emerald-500/60 px-2.5 py-1 text-[10px] text-emerald-100 transition hover:bg-emerald-500/10">Set Active</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Delete this event and all related records?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="dashboard_redirect" value="1">
                                        <input type="hidden" name="dashboard_panel" value="events">
                                        <button class="type-label border border-rose-500/60 px-2.5 py-1 text-[10px] text-rose-200 transition hover:bg-rose-500/10">Delete</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <p class="type-body text-sm text-slate-400">No events yet.</p>
                        @endforelse
                    </div>
                </article>
            </div>

            @elseif ($activePanel === 'workspace')
            <div class="grid h-full gap-3 xl:grid-cols-[minmax(0,1.72fr)_minmax(19rem,.64fr)] 2xl:grid-cols-[minmax(0,1.9fr)_minmax(21rem,.58fr)] text-[15px]">
                @if ($selectedEvent)
                    @php
                        $selectedEventBracketLabel = $selectedEvent->bracketLabel();
                        $selectedEventAllMatches = $selectedEventRounds
                            ->flatMap(fn ($round) => $round->matches->sortBy('match_number')->values())
                            ->values();
                        $selectedEventRoundCount = $selectedEventRounds->count();
                        $pendingMatchCount = $selectedEventAllMatches->where('status', 'pending')->count();
                        $completedMatchCount = $selectedEventAllMatches->where('status', 'completed')->count();
                        $selectedSwissRoundOne = $selectedEventRounds->first(fn ($round) => $round->stage === 'swiss' && (int) $round->round_number === 1);
                        $canReshuffleSelectedSwissRoundOne = $selectedEvent->usesSwissBracket()
                            && (! $selectedSwissRoundOne || $selectedSwissRoundOne->matches->every(fn ($match) => $match->status === 'pending'));
                        $requiresDeckRegistrationNow = $selectedDeckRegistrationTargets->isNotEmpty();
                        $deckRegistrationHeading = $selectedEvent->usesLockedDecks()
                            ? 'Locked Deck Registry'
                            : ($selectedEvent->usesSwissBracket() ? 'Top Cut Deck Registry' : 'Elimination Deck Registry');
                        $deckRegistrationDescription = $selectedEvent->usesLockedDecks()
                            ? 'Every player needs a deck name and Beys 1-3 before the event can start.'
                            : ($selectedEvent->usesSwissBracket()
                                ? 'Register the single-elimination deck lists for qualified players before generating top cut.'
                                : 'Register the elimination deck lists before generating round 1.');
                    @endphp
                    <div class="min-h-0 overflow-y-auto no-scrollbar">
                        @include('dashboard.partials.workspace-bracket-control')
                    </div>

                    <div class="grid min-h-0 content-start gap-2.5 overflow-y-auto no-scrollbar">
                        <article class="flex min-h-0 flex-col overflow-hidden border border-amber-400/25 bg-[linear-gradient(180deg,rgba(251,191,36,0.05)_0%,rgba(2,6,23,0.9)_100%)] p-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="type-title text-sm text-amber-100">Participants</h4>
                                <span class="type-label text-[9px] text-slate-500">{{ $selectedEventParticipants->count() }} total</span>
                            </div>

                            <form action="{{ route('events.participants.store', $selectedEvent) }}" method="POST" class="mt-2.5 grid gap-2.5">
                                @csrf
                                <input type="hidden" name="dashboard_redirect" value="1">
                                    <input type="hidden" name="dashboard_panel" value="workspace">
                                    <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                                    <label class="grid gap-1">
                                        <span class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Nickname</span>
                                        <input name="nickname" value="{{ old('nickname') }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-2.5 py-1.5 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none">
                                    </label>
                                @if ($selectedEvent->usesLockedDecks())
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        <label class="grid gap-1 sm:col-span-2">
                                            <span class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Deck Name</span>
                                            <input name="deck_name" value="{{ old('deck_name') }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-2.5 py-1.5 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none">
                                        </label>
                                        <label class="grid gap-1">
                                            <span class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Bey 1</span>
                                            <input name="deck_bey1" value="{{ old('deck_bey1') }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-2.5 py-1.5 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none">
                                        </label>
                                        <label class="grid gap-1">
                                            <span class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Bey 2</span>
                                            <input name="deck_bey2" value="{{ old('deck_bey2') }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-2.5 py-1.5 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none">
                                        </label>
                                        <label class="grid gap-1 sm:col-span-2">
                                            <span class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Bey 3</span>
                                            <input name="deck_bey3" value="{{ old('deck_bey3') }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-2.5 py-1.5 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none">
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-500">Locked-deck events register the player and deck in one step.</p>
                                @endif
                                @if ($errors->has('nickname') || $errors->has('selected_nicknames') || $errors->has('deck_name') || $errors->has('deck_bey1') || $errors->has('deck_bey2') || $errors->has('deck_bey3'))
                                    <p class="text-xs text-rose-300">
                                        {{ $errors->first('nickname') ?: $errors->first('selected_nicknames') ?: $errors->first('deck_name') ?: $errors->first('deck_bey1') ?: $errors->first('deck_bey2') ?: $errors->first('deck_bey3') }}
                                    </p>
                                @endif
                                <button class="type-label w-fit border border-amber-500/70 bg-amber-500/10 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-500/20">Add Participant</button>
                            </form>

                            <div class="mt-3 min-h-0 flex-1 space-y-1.5 overflow-y-auto no-scrollbar">
                                @forelse ($selectedEventParticipants as $participant)
                                    <div class="flex items-center justify-between gap-2 border border-slate-800/80 bg-slate-950/65 px-3 py-1.5 text-[13px]">
                                        <div class="min-w-0">
                                            <p class="truncate">{{ $participant->player->user->nickname }}</p>
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @if ($participant->hasRegisteredDeck())
                                                    <span class="type-label border border-cyan-500/50 bg-cyan-500/10 px-2 py-0.5 text-[9px] text-cyan-100">{{ $participant->deck_name }}</span>
                                                @elseif ($participant->requiresDeckFor($selectedEvent, $requiresDeckRegistrationNow))
                                                    <span class="type-label border border-rose-500/50 bg-rose-500/10 px-2 py-0.5 text-[9px] text-rose-200">Deck needed</span>
                                                @endif
                                                @if (! $participant->player->user->is_claimed)
                                                    <span class="type-label text-[9px] text-amber-300">auto account</span>
                                                @endif
                                            </div>
                                            @if ($participant->hasRegisteredDeck())
                                                <p class="type-body mt-1 text-[11px] text-slate-400">{{ implode(', ', $participant->registeredBeys()) }}</p>
                                            @endif
                                        </div>
                                        <form action="{{ route('events.participants.destroy', [$selectedEvent, $participant->player]) }}" method="POST" onsubmit="return confirm('Remove this participant from the event?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="dashboard_redirect" value="1">
                                            <input type="hidden" name="dashboard_panel" value="workspace">
                                            <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                                            <button class="type-label border border-rose-500/60 px-2 py-1 text-[9px] text-rose-200 transition hover:bg-rose-500/10">Remove</button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="type-body text-sm text-slate-400">No participants yet.</p>
                                @endforelse
                            </div>
                        </article>

                        <article class="border border-cyan-400/25 bg-[linear-gradient(180deg,rgba(8,47,73,0.09)_0%,rgba(2,6,23,0.9)_100%)] p-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="type-title text-sm text-cyan-100">{{ $deckRegistrationHeading }}</h4>
                                @if ($requiresDeckRegistrationNow)
                                    <span class="type-label text-[9px] text-rose-200">{{ $selectedMissingDeckRegistrations->count() }} missing</span>
                                @endif
                            </div>
                            <p class="mt-2 text-[11px] text-slate-400">{{ $deckRegistrationDescription }}</p>

                            @if ($requiresDeckRegistrationNow)
                                <div class="mt-3 max-h-[18rem] space-y-2 overflow-y-auto no-scrollbar">
                                    @foreach ($selectedDeckRegistrationTargets as $participant)
                                        <form action="{{ route('events.participants.deck.store', [$selectedEvent, $participant->player]) }}" method="POST" class="grid gap-2 border border-slate-800/80 bg-slate-950/65 p-2.5">
                                            @csrf
                                            <input type="hidden" name="dashboard_redirect" value="1">
                                            <input type="hidden" name="dashboard_panel" value="workspace">
                                            <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">

                                            <div class="flex items-center justify-between gap-2">
                                                <p class="text-sm font-semibold text-slate-100">{{ $participant->player->user->nickname }}</p>
                                                <span class="type-label text-[9px] {{ $participant->hasRegisteredDeck() ? 'text-cyan-200' : 'text-rose-200' }}">
                                                    {{ $participant->hasRegisteredDeck() ? 'Ready' : 'Needs deck' }}
                                                </span>
                                            </div>

                                            <label class="grid gap-1">
                                                <span class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Deck Name</span>
                                                <input name="deck_name" value="{{ old('deck_name', $participant->deck_name) }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-2.5 py-1.5 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none">
                                            </label>

                                            <div class="grid gap-2 sm:grid-cols-3">
                                                <label class="grid gap-1">
                                                    <span class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Bey 1</span>
                                                    <input name="deck_bey1" value="{{ old('deck_bey1', $participant->deck_bey1) }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-2.5 py-1.5 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none">
                                                </label>
                                                <label class="grid gap-1">
                                                    <span class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Bey 2</span>
                                                    <input name="deck_bey2" value="{{ old('deck_bey2', $participant->deck_bey2) }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-2.5 py-1.5 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none">
                                                </label>
                                                <label class="grid gap-1">
                                                    <span class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Bey 3</span>
                                                    <input name="deck_bey3" value="{{ old('deck_bey3', $participant->deck_bey3) }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-2.5 py-1.5 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none">
                                                </label>
                                            </div>

                                            <button class="type-label w-fit border border-cyan-500/60 bg-cyan-500/10 px-3 py-1.5 text-[9px] text-cyan-100 transition hover:bg-cyan-500/20">
                                                Save Deck
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-3 text-sm text-slate-500">
                                    {{ $selectedEvent->usesLockedDecks() ? 'All registered participants already have locked decks.' : 'No elimination deck registration is required yet.' }}
                                </p>
                            @endif
                        </article>

                        <article class="border border-slate-800/80 bg-slate-950/55 p-2.5">
                            <h4 class="type-title text-sm text-amber-100">Final Placements</h4>
                            <p class="mt-2 text-xs text-slate-400">Placements are generated automatically after the bracket finishes.</p>

                            <div class="mt-3 space-y-1.5">
                                @forelse ($selectedEventResults as $result)
                                    <div class="flex items-center gap-2 border border-slate-800/80 bg-slate-950/65 px-3 py-1.5 text-[13px]">
                                        <span class="inline-flex min-w-8 justify-center rounded border border-amber-500/40 px-2 py-1 text-[11px] font-semibold text-amber-200">#{{ $result->placement }}</span>
                                        <span>{{ $result->player->user->nickname }}</span>
                                    </div>
                                @empty
                                    <p class="type-body text-sm text-slate-400">
                                        {{ $selectedEvent->bracket_status === 'completed' ? 'No placements were generated.' : 'Placements will appear after the final bracket match.' }}
                                    </p>
                                @endforelse
                            </div>
                        </article>
                    </div>
                @else
                    <div class="flex h-full items-center justify-center border border-slate-800/80 bg-slate-950/55 p-6">
                        <div class="text-center">
                            <p class="type-title text-lg text-slate-100">No active event</p>
                            <p class="type-body mt-2 text-sm text-slate-400">Open the Events panel and set one upcoming event as active to unlock the workspace.</p>
                            <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label mt-4 inline-flex border border-amber-500/70 bg-amber-500/10 px-3 py-2 text-[10px] text-amber-100 transition hover:bg-amber-500/20">Go to Events</a>
                        </div>
                    </div>
                @endif
            </div>

            @else
            <div class="grid h-full gap-3 xl:grid-cols-[minmax(0,1.16fr)_19rem]">
                <article class="min-h-0 overflow-y-auto no-scrollbar border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.22)_0%,rgba(2,6,23,0.92)_100%)] p-4">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="type-kicker text-[10px] text-cyan-300/75">Leaderboard</p>
                            <h3 class="type-headline mt-1 text-xl text-cyan-100">Players</h3>
                        </div>
                        <span class="type-label text-[10px] text-slate-500">{{ $leaderboard->count() }} ranked</span>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400">
                                    <th class="px-3 py-2">Rank</th>
                                    <th class="px-3 py-2">Nickname</th>
                                    <th class="px-3 py-2">Points</th>
                                    <th class="px-3 py-2">Events</th>
                                    <th class="px-3 py-2">Firsts</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($leaderboard as $row)
                                    <tr class="border-b border-slate-900">
                                        <td class="px-3 py-2 text-amber-200">#{{ $row->rank }}</td>
                                        <td class="px-3 py-2">{{ $row->nickname }}</td>
                                        <td class="px-3 py-2 font-semibold text-amber-100">{{ $row->points }}</td>
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
                </article>

                <div class="grid min-h-0 gap-3">
                    <article class="min-h-0 overflow-y-auto no-scrollbar border border-amber-400/30 bg-[linear-gradient(160deg,rgba(251,191,36,0.08)_0%,rgba(2,6,23,0.92)_100%)] p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="type-title text-sm text-amber-100">All Players</p>
                            <span class="type-label text-[10px] text-slate-500">{{ $players->count() }} total</span>
                        </div>
                        <div class="mt-3 space-y-2">
                            @forelse ($players as $player)
                                <div class="border border-slate-800/80 bg-slate-950/65 px-3 py-2 text-sm">
                                    <p class="text-slate-100">{{ $player->user->nickname }}</p>
                                    <p class="text-xs text-slate-400">
                                        player_id: {{ $player->id }}
                                        @if (! $player->user->is_claimed)
                                            - auto account
                                        @endif
                                    </p>
                                </div>
                            @empty
                                <p class="type-body text-sm text-slate-400">No players yet.</p>
                            @endforelse
                        </div>
                    </article>

                    <article class="border border-fuchsia-300/35 bg-[linear-gradient(160deg,rgba(112,26,117,0.16)_0%,rgba(2,6,23,0.92)_100%)] p-4">
                        <p class="type-title text-sm text-fuchsia-100">No Results Yet</p>
                        @if ($playersWithoutResults->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($playersWithoutResults as $player)
                                    <span class="rounded-full border border-slate-700 bg-slate-950/60 px-3 py-1 text-xs">{{ $player->user->nickname }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="type-body mt-3 text-sm text-slate-400">All tracked players already have results.</p>
                        @endif
                    </article>
                </div>
            </div>
            @endif
            </section>
        </section>
    </div>
</x-layouts.app>
