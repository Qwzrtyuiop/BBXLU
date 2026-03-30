<x-layouts.app :title="'Dashboard | BBXLU'" :fullScreen="true" :hideTopSelectors="true" :hideFrameHeader="true">
    @php
        $currentFocus = $ongoingTournament ?? $selectedEvent ?? $latestEvent;
        $currentFocusLink = $currentFocus ? route('dashboard', ['panel' => 'workspace', 'event' => $currentFocus->id]) : route('dashboard', ['panel' => 'events']);
        $overviewEvent = $ongoingTournament ?? $upcomingEvents->first() ?? $selectedEvent ?? $latestEvent;
        $overviewEventLink = $overviewEvent ? route('dashboard', ['panel' => 'workspace', 'event' => $overviewEvent->id]) : route('dashboard', ['panel' => 'events']);
        $overviewEventIsToday = $overviewEvent?->date?->isToday() ?? false;
        $overviewEventLabel = $overviewEvent
            ? ($overviewEvent->status === 'upcoming'
                ? ($overviewEventIsToday ? 'Current Event' : 'Next Event')
                : 'Latest Event')
            : 'Next Event';
        $overviewQueueEvents = $overviewEvent
            ? $upcomingEvents->reject(fn ($event) => $event->id === $overviewEvent->id)
            : $upcomingEvents;
        $canRegisterOverviewEvent = $overviewEvent && $overviewEvent->status === 'upcoming';
        $showRegisterModal = $activePanel === 'overview' && ($errors->has('nickname') || $errors->has('selected_nicknames') || $errors->has('selected_nicknames.*'));
        $oldSelectedNicknames = collect(old('selected_nicknames', []))
            ->map(fn ($nickname) => trim((string) $nickname))
            ->filter()
            ->unique(fn ($nickname) => \Illuminate\Support\Str::lower($nickname))
            ->values();
        $workspaceChallongeLink = $selectedEvent?->resolvedChallongeLink();
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
                            <span class="type-body mt-1 block text-[11px] text-slate-500">live event tools</span>
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
                        <p class="type-kicker text-[10px] text-emerald-300/75">Current Focus</p>
                        <p class="type-title mt-1.5 line-clamp-2 text-sm text-slate-100">{{ $currentFocus?->title ?? 'No active event' }}</p>
                        @if ($currentFocus)
                            <p class="type-label mt-1.5 truncate text-[9px] text-slate-500">{{ $currentFocus->status }} - {{ $currentFocus->date->format('d M') }}</p>
                            <a href="{{ $currentFocusLink }}" class="type-label mt-2 inline-flex w-full items-center justify-center border border-slate-700 px-2 py-1 text-[10px] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">Open</a>
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
                                    <button type="button" data-register-modal-open class="type-label border border-amber-400/70 bg-amber-400/12 px-2.5 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                        Register Players
                                    </button>
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
                                    <a href="{{ route('dashboard', ['panel' => 'workspace', 'event' => $event->id]) }}" class="block border border-slate-800/80 bg-slate-950/65 px-2.5 py-1 transition hover:border-amber-400/55">
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

            @if ($canRegisterOverviewEvent)
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
                            <p class="type-kicker text-[10px] text-amber-300/75">Event Form</p>
                            <h3 class="type-headline mt-1 text-xl text-amber-100">{{ $selectedEvent ? 'Edit Event' : 'Create Event' }}</h3>
                        </div>
                        @if ($selectedEvent)
                            <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">New Event</a>
                        @endif
                    </div>

                    <form action="{{ $selectedEvent ? route('events.update', $selectedEvent) : route('events.store') }}" method="POST" class="mt-4 grid gap-3">
                        @csrf
                        @if ($selectedEvent)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="dashboard_redirect" value="1">
                        <input type="hidden" name="dashboard_panel" value="workspace">
                        @if ($selectedEvent)
                            <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                        @endif

                        <label class="grid gap-1">
                            <span class="text-sm text-slate-300">Title</span>
                            <input name="title" value="{{ old('title', $selectedEvent?->title) }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                        </label>

                        <label class="grid gap-1">
                            <span class="text-sm text-slate-300">Description</span>
                            <textarea name="description" rows="3" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">{{ old('description', $selectedEvent?->description) }}</textarea>
                        </label>

                        <label class="grid gap-1">
                            <span class="text-sm text-slate-300">Challonge URL</span>
                            <input type="url" name="challonge_link" value="{{ old('challonge_link', old('challonge_url', $selectedEvent?->challonge_link ?: $selectedEvent?->challonge_url)) }}" placeholder="https://challonge.com/..." class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
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

                        <button class="type-label w-fit border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-[10px] text-amber-100 transition hover:bg-amber-500/20">
                            {{ $selectedEvent ? 'Update Event' : 'Create Event' }}
                        </button>
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
                            <article class="border {{ $selectedEvent && $selectedEvent->id === $event->id ? 'border-amber-400/60' : 'border-slate-800/80' }} bg-slate-950/65 p-3">
                                <p class="type-label text-[10px] text-slate-500">{{ $event->status }}</p>
                                <p class="type-title mt-1 break-words text-sm text-slate-100">{{ $event->title }}</p>
                                <p class="type-label mt-1 text-[9px] text-slate-500">{{ $event->date->format('d M Y') }} - {{ $event->eventType->name }} - {{ $event->participants_count }} players</p>
                                <p class="type-body mt-2 text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($event->description ?: ($event->location ?: 'No description.'), 88) }}</p>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('dashboard', ['panel' => 'events', 'event' => $event->id]) }}" class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200">Edit Here</a>
                                    <a href="{{ route('dashboard', ['panel' => 'workspace', 'event' => $event->id]) }}" class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">Workspace</a>
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
            <div class="grid h-full gap-3 xl:grid-cols-[minmax(0,1.3fr)_minmax(19rem,.78fr)] 2xl:grid-cols-[minmax(0,1.35fr)_minmax(21rem,.72fr)] text-[15px]">
                @if ($selectedEvent)
                    <article class="min-h-0 overflow-y-auto no-scrollbar border border-emerald-400/30 bg-[linear-gradient(160deg,rgba(6,78,59,0.22)_0%,rgba(2,6,23,0.92)_100%)] p-3">
                        <div class="flex flex-wrap items-start justify-between gap-2.5">
                            <div>
                                <p class="type-kicker text-[10px] text-emerald-300/75">Selected Event</p>
                                <h3 class="type-headline mt-1.5 text-[1.7rem] leading-none text-white">{{ $selectedEvent->title }}</h3>
                                <p class="type-label mt-1.5 text-[9px] text-slate-500">{{ $selectedEvent->status }} - {{ $selectedEvent->date->format('d M Y') }} - {{ $selectedEvent->eventType->name }} - by {{ $selectedEvent->creator->nickname }}</p>
                                @if ($selectedEvent->location)
                                    <p class="type-body mt-1.5 text-sm text-slate-300">Location: {{ $selectedEvent->location }}</p>
                                @endif
                                @if ($selectedEvent->description)
                                    <p class="type-body mt-2 text-sm text-slate-300">{{ \Illuminate\Support\Str::limit($selectedEvent->description, 180) }}</p>
                                @endif

                                <div class="mt-3 grid gap-2 sm:grid-cols-3">
                                    <div class="border border-slate-700/70 bg-slate-950/55 px-3 py-2">
                                        <p class="type-label text-[9px] text-slate-500">Participants</p>
                                        <p class="type-stat mt-1 text-sm text-amber-200">{{ $selectedEventParticipants->count() }}</p>
                                    </div>
                                    <div class="border border-slate-700/70 bg-slate-950/55 px-3 py-2">
                                        <p class="type-label text-[9px] text-slate-500">Results</p>
                                        <p class="type-stat mt-1 text-sm text-amber-200">{{ $selectedEventResults->count() }}</p>
                                    </div>
                                    <div class="border border-slate-700/70 bg-slate-950/55 px-3 py-2">
                                        <p class="type-label text-[9px] text-slate-500">Matches</p>
                                        <p class="type-stat mt-1 text-sm text-amber-200">{{ $selectedEventMatches->count() }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('dashboard', ['panel' => 'events', 'event' => $selectedEvent->id]) }}" class="type-label border border-slate-700 px-2.5 py-1 text-[9px] text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200">Edit Event</a>
                                @if ($workspaceChallongeLink)
                                    <a href="{{ $workspaceChallongeLink }}" target="_blank" rel="noopener noreferrer" class="type-label border border-emerald-500/60 px-2.5 py-1 text-[9px] text-emerald-100 transition hover:bg-emerald-500/10">Challonge</a>
                                @endif
                                <form action="{{ route('events.destroy', $selectedEvent) }}" method="POST" onsubmit="return confirm('Delete this event and all related records?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="dashboard_redirect" value="1">
                                    <input type="hidden" name="dashboard_panel" value="events">
                                    <button class="type-label border border-rose-500/60 px-2.5 py-1 text-[9px] text-rose-200 transition hover:bg-rose-500/10">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-3 grid gap-2.5 xl:grid-cols-[minmax(0,1fr)_minmax(0,.96fr)]">
                            <article class="border border-slate-800/80 bg-slate-950/55 p-3">
                                <h4 class="type-title text-sm text-amber-100">Add Participant</h4>
                                <form action="{{ route('events.participants.store', $selectedEvent) }}" method="POST" class="mt-2.5 grid gap-2.5">
                                    @csrf
                                    <input type="hidden" name="dashboard_redirect" value="1">
                                    <input type="hidden" name="dashboard_panel" value="workspace">
                                    <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                                    <label class="grid gap-1">
                                        <span class="text-sm text-slate-300">Nickname</span>
                                        <input name="nickname" value="{{ old('nickname') }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-1.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                    </label>
                                    <button class="type-label w-fit border border-amber-500/70 bg-amber-500/10 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-500/20">Add Participant</button>
                                </form>

                                <div class="mt-3 space-y-1.5">
                                    @forelse ($selectedEventParticipants as $participant)
                                        <div class="flex items-center justify-between gap-2 border border-slate-800/80 bg-slate-950/65 px-3 py-1.5 text-[13px]">
                                            <div class="min-w-0">
                                                <p class="truncate">{{ $participant->user->nickname }}</p>
                                                @if (! $participant->user->is_claimed)
                                                    <p class="type-label mt-1 text-[9px] text-amber-300">auto account</p>
                                                @endif
                                            </div>
                                            <form action="{{ route('events.participants.destroy', [$selectedEvent, $participant]) }}" method="POST" onsubmit="return confirm('Remove this participant from the event?');">
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

                            <article class="border border-slate-800/80 bg-slate-950/55 p-3">
                                <h4 class="type-title text-sm text-amber-100">Results</h4>
                                <form action="{{ route('events.results.store', $selectedEvent) }}" method="POST" class="mt-2.5 grid gap-2.5">
                                    @csrf
                                    <input type="hidden" name="dashboard_redirect" value="1">
                                    <input type="hidden" name="dashboard_panel" value="workspace">
                                    <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                                    <label class="grid gap-1">
                                        <span class="text-sm text-slate-300">Player</span>
                                        <select name="player_id" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-1.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                            <option value="">Select participant</option>
                                            @foreach ($selectedEventParticipants as $participant)
                                                <option value="{{ $participant->id }}" @selected((string) old('player_id') === (string) $participant->id)>{{ $participant->user->nickname }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="grid gap-1">
                                        <span class="text-sm text-slate-300">Placement</span>
                                        <input type="number" min="1" max="4" name="placement" value="{{ old('placement') }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-1.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                    </label>
                                    <button class="type-label w-fit border border-amber-500/70 bg-amber-500/10 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-500/20">Save Result</button>
                                </form>

                                <div class="mt-3 space-y-1.5">
                                    @forelse ($selectedEventResults as $result)
                                        <div class="flex items-center justify-between gap-2 border border-slate-800/80 bg-slate-950/65 px-3 py-1.5 text-[13px]">
                                            <span>{{ $result->player->user->nickname }} - #{{ $result->placement }}</span>
                                            <form action="{{ route('events.results.destroy', [$selectedEvent, $result]) }}" method="POST" onsubmit="return confirm('Delete this result?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="dashboard_redirect" value="1">
                                                <input type="hidden" name="dashboard_panel" value="workspace">
                                                <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                                                <button class="type-label border border-rose-500/60 px-2 py-1 text-[9px] text-rose-200 transition hover:bg-rose-500/10">Delete</button>
                                            </form>
                                        </div>
                                    @empty
                                        <p class="type-body text-sm text-slate-400">No results yet.</p>
                                    @endforelse
                                </div>
                            </article>
                        </div>
                    </article>

                    <article class="min-h-0 overflow-y-auto no-scrollbar border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.18)_0%,rgba(2,6,23,0.92)_100%)] p-3">
                        <article class="border border-slate-800/80 bg-slate-950/55 p-3">
                            <h4 class="type-title text-sm text-cyan-100">Awards</h4>
                            <form action="{{ route('events.awards.store', $selectedEvent) }}" method="POST" class="mt-2.5 grid gap-2.5">
                                @csrf
                                <input type="hidden" name="dashboard_redirect" value="1">
                                <input type="hidden" name="dashboard_panel" value="workspace">
                                <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                                <label class="grid gap-1">
                                    <span class="text-sm text-slate-300">Award</span>
                                    <select name="award_id" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-1.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                        @foreach ($awards as $award)
                                            <option value="{{ $award->id }}" @selected((string) old('award_id') === (string) $award->id)>{{ $award->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="grid gap-1">
                                    <span class="text-sm text-slate-300">Player</span>
                                    <select name="player_id" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-1.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                        <option value="">Select participant</option>
                                        @foreach ($selectedEventParticipants as $participant)
                                            <option value="{{ $participant->id }}" @selected((string) old('player_id') === (string) $participant->id)>{{ $participant->user->nickname }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <button class="type-label w-fit border border-amber-500/70 bg-amber-500/10 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-500/20">Save Award</button>
                            </form>

                            <div class="mt-3 space-y-1.5">
                                @forelse ($selectedEventAwards as $eventAward)
                                    <div class="flex items-center justify-between gap-2 border border-slate-800/80 bg-slate-950/65 px-3 py-1.5 text-[13px]">
                                        <span>{{ $eventAward->award->name }} - {{ $eventAward->player->user->nickname }}</span>
                                        <form action="{{ route('events.awards.destroy', [$selectedEvent, $eventAward]) }}" method="POST" onsubmit="return confirm('Delete this award assignment?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="dashboard_redirect" value="1">
                                            <input type="hidden" name="dashboard_panel" value="workspace">
                                            <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                                            <button class="type-label border border-rose-500/60 px-2 py-1 text-[9px] text-rose-200 transition hover:bg-rose-500/10">Delete</button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="type-body text-sm text-slate-400">No awards assigned yet.</p>
                                @endforelse
                            </div>
                        </article>

                        <article class="mt-2.5 border border-slate-800/80 bg-slate-950/55 p-3">
                            <h4 class="type-title text-sm text-cyan-100">Matches</h4>
                            <form action="{{ route('events.matches.store', $selectedEvent) }}" method="POST" class="mt-2.5 grid gap-2.5">
                                @csrf
                                <input type="hidden" name="dashboard_redirect" value="1">
                                <input type="hidden" name="dashboard_panel" value="workspace">
                                <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                                <div class="grid gap-2.5 sm:grid-cols-2">
                                    <label class="grid gap-1">
                                        <span class="text-sm text-slate-300">Player 1</span>
                                        <select name="player1_id" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-1.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                            <option value="">Select participant</option>
                                            @foreach ($selectedEventParticipants as $participant)
                                                <option value="{{ $participant->id }}" @selected((string) old('player1_id') === (string) $participant->id)>{{ $participant->user->nickname }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="grid gap-1">
                                        <span class="text-sm text-slate-300">Player 2</span>
                                        <select name="player2_id" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-1.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                            <option value="">Select participant</option>
                                            @foreach ($selectedEventParticipants as $participant)
                                                <option value="{{ $participant->id }}" @selected((string) old('player2_id') === (string) $participant->id)>{{ $participant->user->nickname }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>
                                <div class="grid gap-2.5 sm:grid-cols-3">
                                    <label class="grid gap-1">
                                        <span class="text-sm text-slate-300">P1 Score</span>
                                        <input type="number" min="0" name="player1_score" value="{{ old('player1_score') }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-1.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                    </label>
                                    <label class="grid gap-1">
                                        <span class="text-sm text-slate-300">P2 Score</span>
                                        <input type="number" min="0" name="player2_score" value="{{ old('player2_score') }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-1.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                    </label>
                                    <label class="grid gap-1">
                                        <span class="text-sm text-slate-300">Round</span>
                                        <input type="number" min="1" name="round_number" value="{{ old('round_number') }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-1.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                    </label>
                                </div>
                                <button class="type-label w-fit border border-amber-500/70 bg-amber-500/10 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-500/20">Record Match</button>
                            </form>

                            <div class="mt-3 space-y-1.5">
                                @forelse ($selectedEventMatches as $match)
                                    <div class="flex items-start justify-between gap-2 border border-slate-800/80 bg-slate-950/65 px-3 py-1.5 text-[13px]">
                                        <div class="min-w-0">
                                            <p class="font-medium text-slate-100">{{ $match->player1->user->nickname }} {{ $match->player1_score }} - {{ $match->player2_score }} {{ $match->player2->user->nickname }}</p>
                                            <p class="mt-1 text-[11px] text-slate-400">
                                                Winner: {{ $match->winner->user->nickname }}
                                                @if ($match->round_number)
                                                    - Round {{ $match->round_number }}
                                                @endif
                                            </p>
                                        </div>
                                        <form action="{{ route('events.matches.destroy', [$selectedEvent, $match]) }}" method="POST" onsubmit="return confirm('Delete this match?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="dashboard_redirect" value="1">
                                            <input type="hidden" name="dashboard_panel" value="workspace">
                                            <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                                            <button class="type-label border border-rose-500/60 px-2 py-1 text-[9px] text-rose-200 transition hover:bg-rose-500/10">Delete</button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="type-body text-sm text-slate-400">No matches recorded yet.</p>
                                @endforelse
                            </div>
                        </article>
                    </article>
                @else
                    <div class="flex h-full items-center justify-center border border-slate-800/80 bg-slate-950/55 p-6">
                        <div class="text-center">
                            <p class="type-title text-lg text-slate-100">Select an event</p>
                            <p class="type-body mt-2 text-sm text-slate-400">Open the Events panel and choose an event to manage participants, results, awards, and matches.</p>
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
