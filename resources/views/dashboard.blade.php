<x-layouts.app :title="'Dashboard | BBXLU'" :fullScreen="true" :hideTopSelectors="true" :hideFrameHeader="true" :hideGlobalFeedback="true">
    @php
        $sessionActiveEventId = $dashboardSessionActiveEventId;
        $liveEvents = $liveEvents ?? collect();
        $currentFocus = $ongoingTournament;
        $currentFocusLink = $currentFocus ? route('dashboard', ['panel' => 'workspace', 'event' => $currentFocus->id]) : route('dashboard', ['panel' => 'events']);
        $overviewEvent = $ongoingTournament ?? $upcomingEvents->first() ?? $selectedEvent ?? $latestEvent;
        $overviewEventIsWorkspaceFocus = $overviewEvent && $sessionActiveEventId === $overviewEvent->id;
        $overviewEventLink = $overviewEvent
            ? ($overviewEventIsWorkspaceFocus
                ? route('dashboard', ['panel' => 'workspace', 'event' => $overviewEvent->id])
                : route('dashboard', ['panel' => 'events', 'event' => $overviewEvent->id]))
            : route('dashboard', ['panel' => 'events']);
        $overviewEventIsToday = $overviewEvent?->date?->isToday() ?? false;
        $overviewEventLabel = $overviewEvent
            ? ($overviewEventIsWorkspaceFocus
                ? 'Current Event'
                : ($overviewEvent->status === 'upcoming'
                ? ($overviewEventIsToday ? 'Current Event' : 'Next Event')
                : 'Latest Event'))
            : 'Next Event';
        $overviewQueueEvents = $overviewEvent
            ? $upcomingEvents->reject(fn ($event) => $event->id === $overviewEvent->id)
            : $upcomingEvents;
        $overviewEventStarted = $overviewEvent?->hasStarted() ?? false;
        $overviewEventControlLabel = $overviewEventIsWorkspaceFocus
            ? 'Open Workspace'
            : ($overviewEvent && $overviewEvent->status === 'finished' ? 'Open Event Record' : 'Open Event Tools');
        $overviewEventDeckModeLabel = $overviewEvent
            ? ($overviewEvent->usesLockedDecks() ? 'Locked Decks' : 'Open Decks')
            : 'TBD';
        $overviewEventBracketLabel = $overviewEvent
            ? ($overviewEventStarted ? $overviewEvent->bracketLabel() : 'Not Started')
            : 'No Bracket';
        $overviewEventFocusLabel = $overviewEvent
            ? ($overviewEventIsWorkspaceFocus ? 'Workspace Ready' : ($overviewEvent->status === 'finished' ? 'Archive View' : 'Directory View'))
            : 'Idle';
        $overviewEventLiveLink = $overviewEvent ? route('live.viewer.event', $overviewEvent) : route('live.viewer');
        $latestEventDashboardLink = $latestEvent
            ? route('dashboard', ['panel' => 'events', 'event' => $latestEvent->id])
            : route('dashboard', ['panel' => 'events']);
        $latestEventLiveLink = $latestEvent ? route('live.viewer.event', $latestEvent) : route('live.viewer');
        $overviewTopRows = $leaderboard->take(5);
        $overviewTopLeader = $overviewTopRows->first();
        $selectedEventStarted = $selectedEvent?->hasStarted() ?? false;
        $selectedEventCanEditSwissSettings = $selectedEvent?->canEditSwissSettingsAfterStart() ?? false;
        $canRegisterOverviewEvent = $overviewEvent && $overviewEvent->status === 'upcoming' && ! $overviewEventStarted;
        $overviewRegistrationLabel = $overviewEvent
            ? ($canRegisterOverviewEvent ? 'Open' : ($overviewEventStarted ? 'Closed' : 'Unavailable'))
            : 'Unavailable';
        $hasParticipantRegistrationErrors = $errors->has('nickname')
            || $errors->has('selected_nicknames')
            || $errors->has('selected_nicknames.*');
        $showRegisterModal = $activePanel === 'overview' && $hasParticipantRegistrationErrors;
        $showWorkspaceRegisterModal = $activePanel === 'workspace'
            && $selectedEvent
            && ! $selectedEventStarted
            && ! $selectedEvent->usesLockedDecks()
            && $hasParticipantRegistrationErrors;
        $hasLockedDeckParticipantRegistrationErrors = $activePanel === 'workspace'
            && $selectedEvent
            && ! $selectedEventStarted
            && $selectedEvent->usesLockedDecks()
            && ! old('deck_player_id')
            && (
                $errors->has('nickname')
                || $errors->has('selected_nicknames')
                || $errors->has('selected_nicknames.*')
                || $errors->has('deck_bey1')
                || $errors->has('deck_bey2')
                || $errors->has('deck_bey3')
            );
        $lockedDeckParticipantMode = old('locked_participant_mode') === 'existing' ? 'existing' : 'new';
        $showLockedDeckNewParticipantModal = $hasLockedDeckParticipantRegistrationErrors && $lockedDeckParticipantMode === 'new';
        $showLockedDeckExistingParticipantModal = $hasLockedDeckParticipantRegistrationErrors && $lockedDeckParticipantMode === 'existing';
        $showPlayersRegisterModal = $activePanel === 'players'
            && $playerRegistrationEvent
            && ! $playerRegistrationEvent->usesLockedDecks()
            && $hasParticipantRegistrationErrors;
        $hasDeckRegistrationErrors = $errors->has('deck_bey1')
            || $errors->has('deck_bey2')
            || $errors->has('deck_bey3');
        $hasBulkDeckRegistrationInput = old('decks') !== null;
        $shouldReopenDeckRegistrationModal = $activePanel === 'workspace'
            && $selectedEvent
            && $selectedDeckRegistrationTargets->isNotEmpty()
            && session('deck_modal_reopen') === true;
        $showDeckRegistrationModal = $activePanel === 'workspace'
            && $selectedEvent
            && $selectedDeckRegistrationTargets->isNotEmpty()
            && ($hasDeckRegistrationErrors || $hasBulkDeckRegistrationInput || $shouldReopenDeckRegistrationModal);
        $oldDeckPlayerId = (int) old('deck_player_id', session('deck_modal_focus_player_id', 0));
        $oldSelectedNicknames = collect(old('selected_nicknames', []))
            ->map(fn ($nickname) => trim((string) $nickname))
            ->filter()
            ->unique(fn ($nickname) => \Illuminate\Support\Str::lower($nickname))
            ->values();
        $dashboardEventParameters = $selectedEvent ? ['event' => $selectedEvent->id] : [];
        $toolbarBaseClasses = 'flex w-full min-h-[3.6rem] items-start justify-between border px-2.5 py-2 text-left transition';
        $toolbarActiveClasses = 'border-cyan-300/70 bg-cyan-500/12 text-cyan-100 shadow-[0_12px_24px_rgba(34,211,238,0.16)]';
        $toolbarInactiveClasses = 'border-slate-800/90 bg-slate-950/72 text-slate-300';
        $dashboardErrors = collect($errors->all())
            ->filter()
            ->unique()
            ->values();
    @endphp

    <div
        data-dashboard-shell
        data-dashboard-panel="{{ $activePanel }}"
        data-dashboard-route="{{ route('dashboard') }}"
        data-dashboard-error-state="{{ $dashboardErrors->isNotEmpty() ? 'true' : 'false' }}"
        class="relative mx-auto grid min-h-[calc(100svh-1.9rem)] max-w-[112rem] gap-2.5 overflow-y-auto xl:h-[calc(100svh-1.9rem)] xl:grid-rows-[auto_minmax(0,1fr)]"
    >
        <div data-dashboard-loader class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/88 backdrop-blur-sm transition-opacity duration-200">
            <div class="grid justify-items-center gap-4">
                <div class="relative h-14 w-14">
                    <div class="absolute inset-0 rounded-full border-4 border-cyan-400/15"></div>
                    <div class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-cyan-300 border-r-amber-300"></div>
                </div>
                <div class="text-center">
                    <p class="type-label text-[11px] text-cyan-200">Loading Dashboard</p>
                    <p data-dashboard-loader-label class="mt-1 text-[11px] text-slate-400">Syncing admin view...</p>
                </div>
            </div>
        </div>

        <div data-dashboard-warning class="pointer-events-none fixed inset-0 z-[95] flex items-center justify-center bg-slate-950/92 px-4 opacity-0 backdrop-blur-sm transition-opacity duration-200" aria-hidden="true">
            <div class="w-full max-w-xl border border-rose-400/45 bg-[linear-gradient(160deg,rgba(127,29,29,0.24)_0%,rgba(15,23,42,0.96)_55%,rgba(2,6,23,0.98)_100%)] p-5 shadow-[0_24px_60px_rgba(2,6,23,0.72)] sm:p-6">
                <div class="flex items-start gap-4">
                    <div class="mt-0.5 inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-rose-400/45 bg-rose-500/12 text-xl text-rose-200">
                        !
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="type-kicker text-[10px] text-rose-200/80">Warning</p>
                        <h3 data-dashboard-warning-title class="type-headline mt-1 text-[1.35rem] leading-tight text-amber-100">Dashboard update needs attention</h3>
                        <p data-dashboard-warning-message class="mt-2 text-sm text-slate-300">The dashboard could not finish this request. You can retry the action or reload the page.</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <button type="button" data-dashboard-warning-retry class="type-label inline-flex items-center justify-center border border-amber-400/70 bg-amber-400/12 px-3 py-2 text-[10px] text-amber-100 transition hover:bg-amber-400/20">
                        Retry
                    </button>
                    <button type="button" data-dashboard-warning-reload class="type-label inline-flex items-center justify-center border border-cyan-400/70 bg-cyan-400/10 px-3 py-2 text-[10px] text-cyan-100 transition hover:bg-cyan-400/18">
                        Reload Page
                    </button>
                    <button type="button" data-dashboard-warning-dismiss class="type-label inline-flex items-center justify-center border border-slate-700 bg-slate-950/70 px-3 py-2 text-[10px] text-slate-200 transition hover:border-slate-500 hover:text-slate-100">
                        Continue
                    </button>
                </div>
            </div>
        </div>

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

                <div class="grid gap-1.5 sm:grid-cols-2 xl:flex xl:flex-wrap xl:items-start xl:justify-end xl:justify-self-end 2xl:flex-nowrap">
                    <div class="flex items-center justify-between gap-3 border border-slate-700 bg-slate-950/55 px-2.5 py-1.5 sm:col-span-2 xl:min-w-[12rem]">
                        <div>
                            <p class="type-label text-[9px] text-cyan-100">Auto-Update</p>
                            <p data-dashboard-auto-update-help class="mt-1 text-[9px] text-slate-500">Off by default</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span data-dashboard-auto-update-state class="type-label text-[9px] text-slate-400">Off</span>
                            <button
                                type="button"
                                data-dashboard-auto-update-toggle
                                aria-pressed="false"
                                class="relative inline-flex h-6 w-11 items-center rounded-full border border-slate-700 bg-slate-950/80 px-0.5 transition"
                            >
                                <span
                                    data-dashboard-auto-update-knob
                                    class="h-4 w-4 translate-x-0 rounded-full bg-slate-400 transition-transform duration-200"
                                ></span>
                            </button>
                        </div>
                    </div>
                    <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label inline-flex items-center justify-center border border-amber-400/70 bg-amber-400/12 px-2.5 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">Create Or Edit Event</a>
                    @if ($currentFocus)
                        <a href="{{ $currentFocusLink }}" class="type-label inline-flex items-center justify-center border border-slate-700 bg-slate-950/55 px-2.5 py-1.5 text-[9px] text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200">Open Current Workspace</a>
                    @endif
                    <a href="{{ route('home') }}" class="type-label inline-flex items-center justify-center border border-slate-700 bg-slate-950/55 px-2.5 py-1.5 text-[9px] text-slate-100 transition hover:border-emerald-400 hover:text-emerald-200">Preview Home</a>
                    <form action="{{ route('logout') }}" method="POST" data-dashboard-soft-ignore>
                        @csrf
                        <button class="type-label inline-flex w-full items-center justify-center border border-rose-500/60 bg-rose-500/10 px-2.5 py-1.5 text-[9px] text-rose-200 transition hover:bg-rose-500/20">Logout</button>
                    </form>
                </div>
            </div>

            @if (session('status') || $dashboardErrors->isNotEmpty())
                <div class="mt-2.5 grid gap-1.5">
                    @if (session('status'))
                        <div class="border border-emerald-500/40 bg-emerald-500/12 px-3 py-2 text-[12px] text-emerald-100">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($dashboardErrors->isNotEmpty())
                        <div class="border border-rose-500/40 bg-rose-500/12 px-3 py-2 text-[12px] text-rose-100">
                            <p class="font-semibold uppercase tracking-[0.12em] text-[10px] text-rose-200">Action Needed</p>
                            <div class="mt-1 space-y-1">
                                @foreach ($dashboardErrors->take(5) as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                                @if ($dashboardErrors->count() > 5)
                                    <p class="text-[11px] text-rose-200/80">More issues are shown in the relevant form.</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </section>

        <section class="grid min-h-0 gap-2.5 xl:grid-cols-[11.25rem_minmax(0,1fr)]">
            <aside class="grid min-h-0 grid-rows-[auto_minmax(0,1fr)_auto] gap-2.5 overflow-y-auto border border-slate-800/85 bg-[linear-gradient(160deg,rgba(2,6,23,0.96)_0%,rgba(15,23,42,0.98)_100%)] p-2.5 shadow-[0_16px_36px_rgba(2,6,23,0.34)]">
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
                        <div class="flex items-center justify-between gap-2">
                            <p class="type-kicker text-[10px] text-emerald-300/75">{{ $liveEvents->count() === 1 ? 'Live Event' : 'Live Events' }}</p>
                            @if ($liveEvents->isNotEmpty())
                                <span class="type-label border border-emerald-400/30 bg-emerald-500/10 px-1.5 py-0.5 text-[8px] text-emerald-100">{{ $liveEvents->count() }}</span>
                            @endif
                        </div>
                        @if ($liveEvents->isNotEmpty())
                            <div class="mt-2 space-y-2">
                                @foreach ($liveEvents as $liveEvent)
                                    <a href="{{ route('dashboard', ['panel' => $sessionActiveEventId === $liveEvent->id ? 'workspace' : 'events', 'event' => $liveEvent->id]) }}" class="group block border border-slate-800/80 bg-slate-950/65 px-2.5 py-2 transition hover:border-emerald-400/55">
                                        <p class="type-title line-clamp-2 text-sm text-slate-100">{{ $liveEvent->title }}</p>
                                        <p class="type-label mt-1 truncate text-[9px] text-slate-500">{{ \Illuminate\Support\Str::headline($liveEvent->status) }} - {{ $liveEvent->date->format('d M') }}</p>
                                        <div class="mt-2 flex items-center justify-between gap-2">
                                            <span class="type-label text-[8px] text-emerald-200/80">{{ optional($liveEvent->eventType)->name ?: 'Event' }}</span>
                                            <span class="type-label text-[9px] text-slate-300 transition group-hover:text-emerald-200">Open</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="type-body mt-1.5 text-xs text-slate-400">Toggle events live from the Events panel and they will show here.</p>
                        @endif
                    </article>

                    <!-- <article class="border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.22)_0%,rgba(2,6,23,0.92)_100%)] p-2.5">
                        <p class="type-kicker text-[10px] text-cyan-300/75">Latest Winner</p>
                        @if ($latestChampion && $latestEvent)
                            <p class="type-title mt-1.5 line-clamp-2 text-sm text-slate-100">{{ $latestChampion->player?->user?->nickname ?? 'Unknown player' }}</p>
                            <p class="type-label mt-1.5 line-clamp-2 text-[9px] text-slate-500">{{ $latestEvent->title }}</p>
                        @else
                            <p class="type-body mt-1.5 text-xs text-slate-400">Waiting for a finished event.</p>
                        @endif
                    </article> -->
                </div>
            </aside>

            <section data-dashboard-main class="min-w-0 min-h-0 overflow-x-hidden overflow-y-auto border border-slate-800/85 bg-[linear-gradient(160deg,rgba(2,6,23,0.96)_0%,rgba(15,23,42,0.98)_100%)] p-2.5 shadow-[0_16px_36px_rgba(2,6,23,0.34)] sm:p-3">
            @if ($activePanel === 'overview')
            <div class="grid gap-3 2xl:grid-cols-[minmax(0,1.5fr)_minmax(20rem,0.92fr)]">
                <div class="grid min-h-0 content-start gap-3">
                    <article class="relative overflow-hidden border border-cyan-400/35 bg-[linear-gradient(160deg,rgba(8,47,73,0.26)_0%,rgba(2,6,23,0.95)_42%,rgba(2,6,23,0.99)_100%)] p-4 shadow-[0_20px_42px_rgba(2,6,23,0.42)]">
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-300/0 via-cyan-300/80 to-cyan-300/0"></div>
                        <div class="pointer-events-none absolute -right-12 top-5 h-28 w-28 rounded-full bg-cyan-400/10 blur-3xl"></div>
                        <div class="relative z-10">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="type-kicker text-[10px] text-cyan-300/75">Overview</p>
                                    <h3 class="type-headline mt-1 text-[1.6rem] leading-tight text-cyan-100">
                                        {{ $overviewEvent?->title ?? 'No scheduled event yet' }}
                                    </h3>
                                    @if ($overviewEvent)
                                        <p class="type-body mt-2 text-[12px] text-slate-400">
                                            {{ $overviewEventLabel }} - {{ $overviewEvent->date->format('D, d M Y') }}
                                            @if ($overviewEvent->eventType)
                                                - {{ $overviewEvent->eventType->name }}
                                            @endif
                                            @if ($overviewEvent->location)
                                                - {{ $overviewEvent->location }}
                                            @endif
                                        </p>
                                    @else
                                        <p class="type-body mt-2 text-[12px] text-slate-400">Create an event first so the dashboard can track registration, queue, and bracket activity.</p>
                                    @endif
                                </div>

                                @if ($overviewEvent)
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <span class="type-label border border-cyan-400/35 bg-cyan-500/10 px-2.5 py-1 text-[9px] text-cyan-100">{{ $overviewEventFocusLabel }}</span>
                                        <span class="type-label border border-amber-400/35 bg-amber-500/10 px-2.5 py-1 text-[9px] text-amber-100">{{ $overviewRegistrationLabel }} Registration</span>
                                        @if ($overviewEvent->is_active)
                                            <span class="type-label border border-emerald-400/45 bg-emerald-500/10 px-2.5 py-1 text-[9px] text-emerald-100">Live Slot</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @if ($overviewEvent)
                                <div class="mt-4 grid gap-3 2xl:grid-cols-[minmax(0,1.18fr)_minmax(17.5rem,0.82fr)]">
                                    <div class="min-w-0">
                                        <p class="type-body text-[13px] leading-relaxed text-slate-300">
                                            {{ \Illuminate\Support\Str::limit($overviewEvent->description ?: ($overviewEvent->location ?: 'No description available.'), 200) }}
                                        </p>

                                        <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                            <div class="border border-slate-700/80 bg-slate-950/55 px-3 py-2.5">
                                                <p class="type-label text-[9px] text-slate-500">State</p>
                                                <p class="type-body-strong mt-1 text-[13px] text-slate-100">{{ $overviewEventIsToday && $overviewEvent->status === 'upcoming' ? 'Today' : \Illuminate\Support\Str::headline($overviewEvent->status) }}</p>
                                            </div>
                                            <div class="border border-slate-700/80 bg-slate-950/55 px-3 py-2.5">
                                                <p class="type-label text-[9px] text-slate-500">Deck Mode</p>
                                                <p class="type-body-strong mt-1 text-[13px] text-slate-100">{{ $overviewEventDeckModeLabel }}</p>
                                            </div>
                                            <div class="border border-slate-700/80 bg-slate-950/55 px-3 py-2.5">
                                                <p class="type-label text-[9px] text-slate-500">Bracket</p>
                                                <p class="type-body-strong mt-1 text-[13px] text-slate-100">{{ $overviewEventBracketLabel }}</p>
                                            </div>
                                            <div class="border border-slate-700/80 bg-slate-950/55 px-3 py-2.5">
                                                <p class="type-label text-[9px] text-slate-500">Players</p>
                                                <p class="type-stat mt-1 text-[13px] text-amber-200">{{ $overviewEvent->participants_count ?? 0 }}</p>
                                            </div>
                                            <div class="border border-slate-700/80 bg-slate-950/55 px-3 py-2.5">
                                                <p class="type-label text-[9px] text-slate-500">Venue</p>
                                                <p class="type-body-strong mt-1 truncate text-[13px] text-slate-100">{{ $overviewEvent->location ?: 'TBA' }}</p>
                                            </div>
                                            <div class="border border-slate-700/80 bg-slate-950/55 px-3 py-2.5">
                                                <p class="type-label text-[9px] text-slate-500">Control</p>
                                                <p class="type-body-strong mt-1 text-[13px] text-slate-100">{{ $overviewEventControlLabel }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <a href="{{ $overviewEventLink }}" class="type-label inline-flex items-center justify-center border border-cyan-400/65 bg-cyan-500/10 px-3 py-2 text-[9px] text-cyan-100 transition hover:bg-cyan-500/18">
                                                {{ $overviewEventControlLabel }}
                                            </a>
                                            @if ($canRegisterOverviewEvent)
                                                @if ($overviewEvent->usesLockedDecks())
                                                    <a href="{{ route('dashboard', ['panel' => 'workspace', 'event' => $overviewEvent->id]) }}" class="type-label inline-flex items-center justify-center border border-amber-400/70 bg-amber-400/12 px-3 py-2 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                                        Register Locked Decks
                                                    </a>
                                                @else
                                                    <button type="button" data-register-modal-open class="type-label inline-flex items-center justify-center border border-amber-400/70 bg-amber-400/12 px-3 py-2 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                                        Register Players
                                                    </button>
                                                @endif
                                            @endif
                                            <a href="{{ $overviewEventLiveLink }}" class="type-label inline-flex items-center justify-center border border-emerald-400/55 bg-emerald-500/10 px-3 py-2 text-[9px] text-emerald-100 transition hover:bg-emerald-500/18">
                                                Open Live Viewer
                                            </a>
                                            <a href="{{ route('dashboard', ['panel' => 'events', 'event' => $overviewEvent->id]) }}" class="type-label inline-flex items-center justify-center border border-slate-700 bg-slate-950/55 px-3 py-2 text-[9px] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">
                                                Manage Event
                                            </a>
                                        </div>

                                        @if ($overviewEventStarted)
                                            <p class="mt-3 text-[11px] text-rose-300">Registration is locked because bracket play has already started for this event.</p>
                                        @elseif ($canRegisterOverviewEvent)
                                            <p class="mt-3 text-[11px] text-emerald-300">Registration is open. This is the best place to bring the player list up to date before bracket generation.</p>
                                        @endif
                                    </div>

                                    <div class="grid gap-2.5">
                                        <article class="border border-cyan-400/20 bg-slate-950/45 p-3">
                                            <p class="type-kicker text-[10px] text-cyan-300/75">Event Details</p>
                                            <div class="mt-3 grid gap-2">
                                                <div class="border border-slate-800/80 bg-slate-950/60 px-3 py-2">
                                                    <p class="type-label text-[9px] text-slate-500">Created By</p>
                                                    <p class="type-body-strong mt-1 text-[13px] text-slate-100">{{ optional($overviewEvent->creator)->nickname ?: 'System' }}</p>
                                                </div>
                                                <div class="border border-slate-800/80 bg-slate-950/60 px-3 py-2">
                                                    <p class="type-label text-[9px] text-slate-500">Date</p>
                                                    <p class="type-body-strong mt-1 text-[13px] text-slate-100">{{ $overviewEventIsToday ? 'Today' : $overviewEvent->date->format('l') }}</p>
                                                </div>
                                                <div class="border border-slate-800/80 bg-slate-950/60 px-3 py-2">
                                                    <p class="type-label text-[9px] text-slate-500">Queue</p>
                                                    <p class="type-stat mt-1 text-[13px] text-amber-200">{{ $overviewQueueEvents->count() }}</p>
                                                </div>
                                            </div>
                                        </article>

                                        <article class="border border-amber-400/20 bg-[linear-gradient(160deg,rgba(251,191,36,0.09)_0%,rgba(2,6,23,0.9)_100%)] p-3">
                                            <p class="type-kicker text-[10px] text-amber-300/75">Next Step</p>
                                            @if ($canRegisterOverviewEvent)
                                                <p class="type-title mt-2 text-[15px] text-amber-100">Complete registration and verify the deck mode before building the bracket.</p>
                                                <p class="type-body mt-2 text-[12px] text-slate-400">Use the registration action below if names still need to be added, then jump into the event tools to start the round flow.</p>
                                            @elseif ($overviewEvent && $overviewEventStarted && $overviewEvent->status === 'upcoming')
                                                <p class="type-title mt-2 text-[15px] text-amber-100">Bracket play is already moving.</p>
                                                <p class="type-body mt-2 text-[12px] text-slate-400">Open the workspace to record match results, manage rounds, and keep the live board accurate.</p>
                                            @elseif ($overviewEvent && $overviewEvent->status === 'finished')
                                                <p class="type-title mt-2 text-[15px] text-amber-100">This event is now in archive mode.</p>
                                                <p class="type-body mt-2 text-[12px] text-slate-400">Review results, confirm awards, and move your attention to the next event in queue.</p>
                                            @else
                                                <p class="type-title mt-2 text-[15px] text-amber-100">Set the next event up for action.</p>
                                                <p class="type-body mt-2 text-[12px] text-slate-400">Keep the event directory current so the workspace and homepage always reflect the right focus.</p>
                                            @endif
                                        </article>
                                    </div>
                                </div>
                            @else
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label inline-flex items-center justify-center border border-amber-400/70 bg-amber-400/12 px-3 py-2 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                        Create Event
                                    </a>
                                    <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label inline-flex items-center justify-center border border-slate-700 bg-slate-950/55 px-3 py-2 text-[9px] text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200">
                                        Open Events Directory
                                    </a>
                                </div>
                            @endif
                        </div>
                    </article>

                    <div class="grid min-h-0 gap-3 2xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                        <article class="min-h-0 overflow-hidden border border-amber-400/30 bg-[linear-gradient(160deg,rgba(251,191,36,0.08)_0%,rgba(2,6,23,0.92)_100%)] p-3">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <p class="type-kicker text-[10px] text-amber-300/75">Queue</p>
                                    <h3 class="type-title mt-1 text-sm text-amber-100">Upcoming Events</h3>
                                </div>
                                <div class="text-right">
                                    <p class="type-stat text-sm leading-none text-amber-200">{{ $overviewQueueEvents->count() }}</p>
                                    <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label mt-1 block text-[9px] text-slate-300 hover:text-amber-200">Manage</a>
                                </div>
                            </div>
                            <div class="mt-3 space-y-2">
                                @forelse ($overviewQueueEvents->take(4) as $event)
                                    <a href="{{ route('dashboard', ['panel' => $sessionActiveEventId === $event->id ? 'workspace' : 'events', 'event' => $event->id]) }}" class="group flex items-center gap-3 border border-slate-800/80 bg-slate-950/65 p-2.5 transition hover:border-amber-400/55 hover:bg-slate-950/80">
                                        <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center border border-amber-400/20 bg-amber-500/8 text-center">
                                            <p class="type-label text-[8px] text-amber-200/80">{{ strtoupper($event->date->format('M')) }}</p>
                                            <p class="type-stat mt-1 text-lg leading-none text-amber-100">{{ $event->date->format('d') }}</p>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="type-title truncate text-[13px] text-slate-100">{{ $event->title }}</p>
                                            <p class="type-label mt-1 truncate text-[9px] text-slate-500">{{ $event->eventType->name }} - {{ $event->location ?: 'TBA' }}</p>
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                <span class="type-label border border-slate-800/80 bg-slate-950/60 px-2 py-0.5 text-[8px] text-slate-400">{{ $event->usesLockedDecks() ? 'locked decks' : 'open decks' }}</span>
                                                <span class="type-label border border-slate-800/80 bg-slate-950/60 px-2 py-0.5 text-[8px] text-slate-400">{{ $event->participants_count }} players</span>
                                            </div>
                                        </div>
                                        <span class="type-label shrink-0 text-[9px] text-slate-400 transition group-hover:text-amber-200">Open</span>
                                    </a>
                                @empty
                                    <p class="type-body text-sm text-slate-400">No additional upcoming events. Add one from the Events panel so the queue stays visible to admins.</p>
                                @endforelse
                            </div>
                        </article>

                        <article class="min-h-0 overflow-hidden border border-cyan-400/35 bg-[linear-gradient(165deg,rgba(8,47,73,0.28)_0%,rgba(2,6,23,0.93)_42%,rgba(2,6,23,0.99)_100%)] p-3 shadow-[0_16px_34px_rgba(2,6,23,0.36)]">
                            <div class="pointer-events-none h-px w-full bg-gradient-to-r from-cyan-300/0 via-cyan-300/65 to-cyan-300/0"></div>
                            <div class="mt-3 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="type-kicker text-[10px] text-cyan-300/75">Rankings</p>
                                        <span title="{{ $leaderboardScoreTooltip }}" class="type-label inline-flex cursor-help items-center border border-cyan-400/30 bg-cyan-400/10 px-2 py-1 text-[8px] text-cyan-100">Score</span>
                                    </div>
                                    <h3 class="type-title mt-1 text-sm text-cyan-100">Top Bladers</h3>
                                </div>
                                <a href="{{ route('dashboard', ['panel' => 'players']) }}" class="type-label shrink-0 text-[9px] text-slate-300 hover:text-cyan-200">Players</a>
                            </div>
                            @if ($overviewTopLeader)
                                <div class="mt-3 border border-cyan-400/25 bg-slate-950/50 p-3">
                                    <p class="type-label text-[8px] text-slate-500">Current #1</p>
                                    <div class="mt-2 flex items-end justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="type-title truncate text-[15px] text-cyan-100">{{ $overviewTopLeader->nickname }}</p>
                                            <p class="type-body mt-1 text-[12px] text-slate-400">{{ $overviewTopLeader->events_played }} events - {{ $overviewTopLeader->first_places }} wins</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="type-stat text-2xl leading-none text-amber-200">{{ $overviewTopLeader->score_display ?? $overviewTopLeader->points }}</p>
                                            <p class="type-label mt-1 cursor-help text-[8px] text-slate-500" title="{{ $leaderboardScoreTooltip }}">score</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 space-y-1.5">
                                    @forelse ($overviewTopRows->skip(1) as $row)
                                        <div class="flex items-center justify-between gap-3 border border-slate-800/80 bg-slate-950/65 px-3 py-2">
                                            <div class="flex min-w-0 items-center gap-2">
                                                <span class="type-label inline-flex h-6 w-6 items-center justify-center border border-slate-700/80 bg-slate-950/70 text-[9px] text-amber-200">{{ $row->rank }}</span>
                                                <p class="type-display-copy truncate text-[13px] text-slate-100">{{ $row->nickname }}</p>
                                            </div>
                                            <p class="type-stat text-[13px] text-cyan-200">{{ $row->score_display ?? $row->points }}</p>
                                        </div>
                                    @empty
                                        <p class="type-body text-sm text-slate-400">No ranking data yet.</p>
                                    @endforelse
                                </div>
                            @else
                                <p class="mt-3 type-body text-sm text-slate-400">No ranking data yet. Finish an event to populate the season snapshot.</p>
                            @endif
                        </article>
                    </div>
                </div>

                <div class="grid min-h-0 content-start gap-3">
                    <article class="min-h-0 overflow-hidden border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.18)_0%,rgba(2,6,23,0.92)_100%)] p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="type-kicker text-[10px] text-cyan-300/75">Latest Event</p>
                                <h3 class="type-title mt-1 text-sm text-cyan-100">Completed Event</h3>
                            </div>
                            @if ($latestEvent)
                                <div class="flex items-center gap-2">
                                    <a href="{{ $latestEventDashboardLink }}" class="type-label text-[9px] text-slate-300 hover:text-cyan-200">Record</a>
                                    <a href="{{ $latestEventLiveLink }}" class="type-label text-[9px] text-slate-300 hover:text-emerald-200">Viewer</a>
                                </div>
                            @endif
                        </div>

                        @if ($latestEvent)
                            <div class="mt-3 border border-cyan-400/20 bg-slate-950/55 p-3">
                                <p class="type-title text-[15px] text-cyan-100">{{ $latestEvent->title }}</p>
                                <p class="type-label mt-1 text-[9px] text-slate-500">{{ $latestEvent->date->format('d M Y') }} - {{ optional($latestEvent->eventType)->name ?: 'Event' }}</p>
                            </div>

                            <div class="mt-3 border border-emerald-400/20 bg-[linear-gradient(160deg,rgba(6,78,59,0.15)_0%,rgba(2,6,23,0.9)_100%)] p-3">
                                <p class="type-kicker text-[10px] text-emerald-300/75">Champion</p>
                                <div class="mt-2 flex items-end justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="type-title truncate text-[15px] text-emerald-100">{{ $latestChampion?->player?->user?->nickname ?? 'Unknown player' }}</p>
                                        <p class="type-body mt-1 text-[12px] text-slate-400">Latest winner on record</p>
                                    </div>
                                    <span class="type-label border border-emerald-400/35 bg-emerald-500/10 px-2 py-1 text-[9px] text-emerald-100">1st Place</span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <p class="type-title text-[13px] text-slate-100">Placements</p>
                                    <span class="type-label text-[8px] text-slate-500">top 4</span>
                                </div>
                                <div class="space-y-1.5">
                                    @forelse ($latestEventPlacements as $result)
                                        <div class="flex items-center gap-2.5 border border-slate-800/80 bg-slate-950/65 px-2.5 py-1.5">
                                            <span class="type-stat w-6 text-[13px] text-amber-300">{{ $result->placement }}</span>
                                            <span class="type-display-copy min-w-0 flex-1 truncate text-[13px] text-slate-100">{{ $result->player->user->nickname }}</span>
                                        </div>
                                    @empty
                                        <p class="type-body text-sm text-slate-400">No placements recorded yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        @else
                            <p class="mt-3 type-body text-sm text-slate-400">Waiting for the first finished event before the result archive can populate.</p>
                        @endif
                    </article>

                    <article class="border border-fuchsia-300/35 bg-[linear-gradient(160deg,rgba(112,26,117,0.16)_0%,rgba(2,6,23,0.92)_100%)] p-3">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <p class="type-kicker text-[10px] text-fuchsia-300/75">Awards</p>
                                <h3 class="type-title mt-1 text-sm text-fuchsia-100">Award Leaders</h3>
                            </div>
                            <span class="type-label text-[8px] text-slate-500">top 3</span>
                        </div>
                        <div class="mt-3 grid gap-2">
                            @forelse ($awardLeaders->take(3) as $row)
                                <div class="border border-slate-800/80 bg-slate-950/65 p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="type-label text-[8px] text-slate-500">{{ $row['description'] }}</p>
                                            <p class="type-title mt-1 text-[14px] text-fuchsia-100">{{ $row['title'] }}</p>
                                        </div>
                                        <span class="type-label shrink-0 border border-slate-800/80 bg-slate-950/60 px-2 py-1 text-[8px] text-slate-400">{{ $row['award_name'] }}</span>
                                    </div>
                                    <div class="mt-3 flex items-end justify-between gap-3">
                                        <p class="type-display-copy truncate text-[13px] text-slate-100">{{ $row['nickname'] ?: 'No data yet' }}</p>
                                        <p class="type-stat text-[15px] text-amber-200">{{ $row['total'] }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="type-body text-sm text-slate-400">No award data yet.</p>
                            @endforelse
                        </div>
                    </article>
                </div>
            </div>

            @if ($canRegisterOverviewEvent && ! $overviewEvent->usesLockedDecks())
                @include('dashboard.partials.register-participants-modal', [
                    'registrationEvent' => $overviewEvent,
                    'registrationPanel' => 'overview',
                    'showRegistrationModal' => $showRegisterModal,
                ])
            @endif

            @elseif ($activePanel === 'events')
            <div class="grid h-full gap-3 xl:grid-cols-[minmax(0,.94fr)_minmax(22rem,1.06fr)]">
                <article class="min-h-0 overflow-y-auto no-scrollbar border border-amber-400/30 bg-[linear-gradient(160deg,rgba(251,191,36,0.08)_0%,rgba(2,6,23,0.92)_100%)] p-4">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="type-kicker text-[10px] text-amber-300/75">{{ $selectedEvent ? ($selectedEventStarted ? 'Locked Mode' : 'Edit Mode') : 'Create Mode' }}</p>
                            <h3 class="type-headline mt-1 text-xl text-amber-100">{{ $selectedEvent ? ($selectedEventStarted ? 'Event Locked' : 'Edit Event') : 'Create Event' }}</h3>
                            <p class="type-body mt-1 text-xs text-slate-400">
                                {{ $selectedEvent
                                    ? ($selectedEventStarted ? 'This event has already started. Editing unavailable.' : 'Updating the selected event from the directory.')
                                    : 'Fill out the form below to create a new event.' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($selectedEvent && $sessionActiveEventId === $selectedEvent->id)
                                <span class="type-label border border-emerald-400/60 bg-emerald-500/10 px-2.5 py-1 text-[10px] text-emerald-100">Active Event</span>
                            @elseif ($selectedEvent && in_array($selectedEvent->status, ['upcoming', 'finished'], true))
                                <form action="{{ route('events.activate', $selectedEvent) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="dashboard_redirect" value="1">
                                    <input type="hidden" name="dashboard_panel" value="events">
                                    <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                                    <button class="type-label border border-emerald-500/60 bg-emerald-500/10 px-2.5 py-1 text-[10px] text-emerald-100 transition hover:bg-emerald-500/20">Set Active</button>
                                </form>
                            @endif
                            @if ($selectedEvent && in_array($selectedEvent->status, ['upcoming', 'finished'], true))
                                <form action="{{ route('events.live.toggle', $selectedEvent) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="dashboard_redirect" value="1">
                                    <input type="hidden" name="dashboard_panel" value="events">
                                    <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
                                    <button class="type-label border {{ $selectedEvent->is_active ? 'border-cyan-400/60 bg-cyan-500/10 text-cyan-100 hover:bg-cyan-500/20' : 'border-fuchsia-500/60 text-fuchsia-100 hover:bg-fuchsia-500/10' }} px-2.5 py-1 text-[10px] transition">
                                        {{ $selectedEvent->is_active ? 'Stop Live' : 'Go Live' }}
                                    </button>
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
                                <p class="mt-1 text-sm text-slate-100">
                                    {{ $selectedEventStarted
                                        ? ($selectedEventCanEditSwissSettings ? 'Swiss Config Open: '.$selectedEvent->title : 'Locked: '.$selectedEvent->title)
                                        : 'Editing: '.$selectedEvent->title }}
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $selectedEventStarted
                                        ? ($selectedEventCanEditSwissSettings
                                            ? 'Bracket play has started. Only Swiss rounds and top cut size can still be adjusted before top cut is generated.'
                                            : 'Bracket play has started. Event details can no longer be changed.')
                                        : 'Use Create New Event to clear the form and start a fresh entry.' }}
                                </p>
                            @else
                                <p class="mt-1 text-sm text-slate-100">Ready to create a new event.</p>
                                <p class="mt-1 text-xs text-slate-400">Select an existing event from the directory only if you want to load it into edit mode.</p>
                            @endif
                        </div>

                        <label class="grid gap-1">
                            <span class="text-sm text-slate-300">Title</span>
                            <input name="title" value="{{ old('title', $selectedEvent?->title) }}" required @disabled($selectedEventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                        </label>

                        <label class="grid gap-1">
                            <span class="text-sm text-slate-300">Description</span>
                            <textarea name="description" rows="3" @disabled($selectedEventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">{{ old('description', $selectedEvent?->description) }}</textarea>
                        </label>

                        <div class="grid gap-3">
                        <label class="grid gap-1">
                            <span class="text-sm text-slate-300">Bracket Type</span>
                            <select name="bracket_type" required @disabled($selectedEventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                                    <option value="single_elim" @selected(old('bracket_type', $selectedEvent?->bracket_type ?? 'single_elim') === 'single_elim')>Single Elimination</option>
                                    <option value="swiss_single_elim" @selected(old('bracket_type', $selectedEvent?->bracket_type) === 'swiss_single_elim')>Swiss + Single Elimination</option>
                                </select>
                            </label>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Swiss Rounds</span>
                                <input type="number" min="1" max="12" name="swiss_rounds" value="{{ old('swiss_rounds', $selectedEvent?->swiss_rounds ?: 5) }}" @disabled($selectedEventStarted && ! $selectedEventCanEditSwissSettings) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                            </label>

                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Top Cut Size</span>
                                <input type="number" min="2" max="64" name="top_cut_size" value="{{ old('top_cut_size', $selectedEvent?->top_cut_size ?: 8) }}" @disabled($selectedEventStarted && ! $selectedEventCanEditSwissSettings) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                            </label>
                        </div>

                        <p class="text-xs text-slate-500">
                            {{ $selectedEventCanEditSwissSettings
                                ? 'Swiss settings stay editable until the first top cut round is generated.'
                                : 'Swiss settings are only used when the event runs Swiss into a top cut.' }}
                        </p>
                        <p class="text-xs text-slate-500">Top cut accepts any value from 2 to 64. The bracket will pad to the next elimination size with byes when needed.</p>

                        <label class="grid gap-2 border border-slate-800/80 bg-slate-950/55 px-3 py-3">
                            <span class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    name="is_lock_deck"
                                    value="1"
                                    @checked((bool) old('is_lock_deck', $selectedEvent?->is_lock_deck))
                                    @disabled($selectedEventStarted)
                                    class="mt-0.5 h-4 w-4 rounded border-slate-700 bg-slate-950 text-amber-400 focus:ring-amber-400 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                <span>
                                    <span class="block text-sm text-slate-100">Lock deck from registration</span>
                                    <span class="mt-1 block text-xs text-slate-500">When enabled, every player must register Beys 1-3 before round 1. When disabled, deck registration is only required before players enter single elimination.</span>
                                </span>
                            </span>
                        </label>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Event Type</span>
                                <select name="event_type_id" required @disabled($selectedEventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                                    @foreach ($eventTypes as $type)
                                        <option value="{{ $type->id }}" @selected((string) old('event_type_id', $selectedEvent?->event_type_id) === (string) $type->id)>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Date</span>
                                <input type="date" name="date" value="{{ old('date', optional($selectedEvent?->date)->format('Y-m-d')) }}" required @disabled($selectedEventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                            </label>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Location</span>
                                <input name="location" value="{{ old('location', $selectedEvent?->location) }}" @disabled($selectedEventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                            </label>

                            <label class="grid gap-1">
                                <span class="text-sm text-slate-300">Status</span>
                                <select name="status" required @disabled($selectedEventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                                    <option value="upcoming" @selected(old('status', $selectedEvent?->status ?? 'upcoming') === 'upcoming')>Upcoming</option>
                                    <option value="finished" @selected(old('status', $selectedEvent?->status) === 'finished')>Finished</option>
                                </select>
                            </label>
                        </div>

                        <label class="grid gap-1">
                            <span class="text-sm text-slate-300">Created By</span>
                            <input value="{{ $selectedEvent?->creator?->nickname ?? auth()->user()->nickname }}" readonly class="rounded-lg border border-slate-800 bg-slate-900/80 px-3 py-2 text-slate-400 focus:outline-none">
                        </label>

                        <div class="flex flex-wrap gap-2 pt-1">
                            <button @disabled($selectedEventStarted && ! $selectedEventCanEditSwissSettings) class="type-label border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-[10px] text-amber-100 transition hover:bg-amber-500/20 disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                                {{ $selectedEventCanEditSwissSettings ? 'Update Swiss Settings' : ($selectedEvent ? 'Update Event' : 'Create Event') }}
                            </button>
                        </div>
                    </form>
                </article>

                <article class="min-h-0 overflow-y-auto no-scrollbar border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.18)_0%,rgba(2,6,23,0.92)_100%)] p-4">
                    @php
                        $upcomingAdminEvents = $adminEvents->where('status', 'upcoming')->values();
                        $finishedAdminEvents = $adminEvents->where('status', 'finished')->values();
                    @endphp
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="type-kicker text-[10px] text-cyan-300/75">Event Directory</p>
                            <h3 class="type-headline mt-1 text-xl text-cyan-100">All Events</h3>
                        </div>
                        <span class="type-label text-[10px] text-slate-500">{{ $adminEvents->count() }} total</span>
                    </div>

                    <div class="mt-4 grid items-start gap-4 xl:grid-cols-2">
                        <section class="grid min-h-0 content-start self-start gap-3">
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2">
                                <div>
                                    <p class="type-title text-sm text-amber-100">Upcoming</p>
                                    <p class="type-body mt-1 text-[11px] text-slate-500">Active and scheduled events.</p>
                                </div>
                                <span class="type-label text-[10px] text-slate-500">{{ $upcomingAdminEvents->count() }}</span>
                            </div>

                            <div class="space-y-3">
                                @forelse ($upcomingAdminEvents as $event)
                                    @include('dashboard.partials.event-directory-card', ['event' => $event])
                                @empty
                                    <p class="type-body text-sm text-slate-400">No upcoming events.</p>
                                @endforelse
                            </div>
                        </section>

                        <section class="grid min-h-0 content-start self-start gap-3">
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2">
                                <div>
                                    <p class="type-title text-sm text-cyan-100">Finished</p>
                                    <p class="type-body mt-1 text-[11px] text-slate-500">Completed events and archived brackets.</p>
                                </div>
                                <span class="type-label text-[10px] text-slate-500">{{ $finishedAdminEvents->count() }}</span>
                            </div>

                            <div class="space-y-3">
                                @forelse ($finishedAdminEvents as $event)
                                    @include('dashboard.partials.event-directory-card', ['event' => $event])
                                @empty
                                    <p class="type-body text-sm text-slate-400">No finished events yet.</p>
                                @endforelse
                            </div>
                        </section>
                    </div>
                </article>

                <div class="hidden">
                    @foreach ($adminEvents as $event)
                        <template id="event-preview-template-{{ $event->id }}">
                            @include('dashboard.partials.event-preview-modal-body', [
                                'event' => $event,
                                'preview' => $adminEventPreviews->get($event->id, []),
                            ])
                        </template>
                    @endforeach
                </div>
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
                            ? 'Every player needs Beys 1-3 registered before the event can start.'
                            : ($selectedEvent->usesSwissBracket()
                                ? 'Register Beys 1-3 for qualified players before generating top cut.'
                                : 'Register Beys 1-3 before generating round 1.');
                        $autoAccountParticipantCount = $selectedEventParticipants->filter(fn ($participant) => ! $participant->player->user->is_claimed)->count();
                        $deckReadyParticipantCount = $selectedEventParticipants->filter(fn ($participant) => $participant->hasRegisteredDeck())->count();
                    @endphp
                    <div class="min-h-0 overflow-y-auto no-scrollbar">
                        @include('dashboard.partials.workspace-bracket-control')
                    </div>

                    <div class="grid min-h-0 content-start gap-2.5 overflow-y-auto no-scrollbar">
                        <article class="border border-amber-400/25 bg-[linear-gradient(180deg,rgba(251,191,36,0.05)_0%,rgba(2,6,23,0.9)_100%)] p-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="type-title text-sm text-amber-100">Participants</h4>
                                <span class="type-label text-[9px] text-slate-500">{{ $selectedEventParticipants->count() }} total</span>
                            </div>

                            @if ($selectedEventStarted)
                                <div class="mt-2.5 grid gap-2 border border-rose-500/30 bg-rose-500/[0.06] p-3">
                                    <p class="text-[11px] text-rose-200">Participant registration is locked because bracket play has already started for this event.</p>
                                    <p class="text-[11px] text-slate-500">You can still review the roster and continue match or deck operations, but the participant list can no longer change.</p>
                                </div>
                            @elseif ($selectedEvent->usesLockedDecks())
                                <div class="mt-2.5 grid gap-2 border border-slate-800/80 bg-slate-950/55 p-3">
                                    <p class="text-[11px] text-slate-400">Locked-deck events register one player at a time together with Beys 1-3.</p>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            data-locked-participant-open="locked-deck-new-player"
                                            class="type-label border border-amber-400/70 bg-amber-400/12 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20"
                                        >
                                            Add New Player
                                        </button>
                                        <button
                                            type="button"
                                            data-locked-participant-open="locked-deck-existing-player"
                                            @disabled($registerableUsers->isEmpty())
                                            class="type-label border border-cyan-500/60 bg-cyan-500/10 px-3 py-1.5 text-[9px] text-cyan-100 transition hover:bg-cyan-500/20 disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500"
                                        >
                                            Select From Existing
                                        </button>
                                        <span class="text-[11px] text-slate-500">{{ $registerableUsers->count() }} existing available</span>
                                    </div>
                                    @if ($hasLockedDeckParticipantRegistrationErrors)
                                        <p class="text-xs text-rose-300">
                                            {{ $errors->first('nickname') ?: $errors->first('selected_nicknames') ?: $errors->first('selected_nicknames.*') ?: $errors->first('deck_bey1') ?: $errors->first('deck_bey2') ?: $errors->first('deck_bey3') }}
                                        </p>
                                    @endif
                                </div>
                            @else
                                <div class="mt-2.5 grid gap-2 border border-slate-800/80 bg-slate-950/55 p-3">
                                    <p class="text-[11px] text-slate-400">Use the same multi-add flow as Overview to pull registered users or create new nicknames before the bracket starts.</p>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button type="button" data-register-modal-open class="type-label border border-amber-400/70 bg-amber-400/12 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                            Register Players
                                        </button>
                                        <span class="text-[11px] text-slate-500">{{ $registerableUsers->count() }} available</span>
                                    </div>
                                    @if ($errors->has('nickname') || $errors->has('selected_nicknames') || $errors->has('selected_nicknames.*'))
                                        <p class="text-xs text-rose-300">
                                            {{ $errors->first('nickname') ?: $errors->first('selected_nicknames') ?: $errors->first('selected_nicknames.*') }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-3 grid gap-2 border border-slate-800/80 bg-slate-950/55 p-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="grid gap-1 text-[11px] text-slate-400">
                                        <p>{{ $selectedEventParticipants->count() }} total participants</p>
                                        <p>{{ $deckReadyParticipantCount }} deck ready / {{ $autoAccountParticipantCount }} auto accounts</p>
                                    </div>
                                    <button type="button" data-participants-modal-open class="type-label border border-amber-400/70 bg-amber-400/12 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                        Open Participants
                                    </button>
                                </div>

                                <p class="text-[11px] text-slate-500">
                                    {{ $selectedEventStarted
                                        ? 'Open to review the locked roster and participant deck status.'
                                        : ($selectedEventParticipants->isNotEmpty()
                                            ? 'Open to view the full roster, deck tags, and removal controls.'
                                            : 'No participants yet. Add players, then open the modal when the roster is ready.') }}
                                </p>
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
                                <div class="mt-3 grid gap-2 border border-slate-800/80 bg-slate-950/55 p-3">
                                    <div class="grid gap-1 text-[11px] text-slate-400">
                                        <p>{{ $selectedDeckRegistrationTargets->count() }} players in registry</p>
                                        <p>{{ $selectedDeckRegistrationTargets->count() - $selectedMissingDeckRegistrations->count() }} ready / {{ $selectedMissingDeckRegistrations->count() }} missing</p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button type="button" data-deck-modal-open class="type-label border border-cyan-500/60 bg-cyan-500/10 px-3 py-1.5 text-[9px] text-cyan-100 transition hover:bg-cyan-500/20">
                                            Open Deck Registry
                                        </button>
                                        <span class="text-[11px] text-slate-500">Register or update Beys 1-3 in the modal.</span>
                                    </div>
                                    @if ($hasDeckRegistrationErrors)
                                        <p class="text-xs text-rose-300">
                                            {{ $errors->first('deck_bey1') ?: $errors->first('deck_bey2') ?: $errors->first('deck_bey3') }}
                                        </p>
                                    @endif
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

                    @if (! $selectedEvent->usesLockedDecks())
                        @include('dashboard.partials.register-participants-modal', [
                            'registrationEvent' => $selectedEvent,
                            'registrationPanel' => 'workspace',
                            'showRegistrationModal' => $showWorkspaceRegisterModal,
                        ])
                    @else
                        @include('dashboard.partials.locked-deck-participant-modal', [
                            'registrationEvent' => $selectedEvent,
                            'registerableUsers' => $registerableUsers,
                            'mode' => 'new',
                            'modalId' => 'locked-deck-new-player',
                            'showModal' => $showLockedDeckNewParticipantModal,
                        ])
                        @include('dashboard.partials.locked-deck-participant-modal', [
                            'registrationEvent' => $selectedEvent,
                            'registerableUsers' => $registerableUsers,
                            'mode' => 'existing',
                            'modalId' => 'locked-deck-existing-player',
                            'showModal' => $showLockedDeckExistingParticipantModal,
                        ])
                    @endif
                    @include('dashboard.partials.participants-modal', [
                        'participantEvent' => $selectedEvent,
                        'participantEventParticipants' => $selectedEventParticipants,
                        'participantDeckGateActive' => $requiresDeckRegistrationNow,
                    ])
                    @if ($requiresDeckRegistrationNow)
                        @include('dashboard.partials.deck-registration-modal', [
                            'deckRegistrationEvent' => $selectedEvent,
                            'deckRegistrationTargets' => $selectedDeckRegistrationTargets,
                            'deckRegistrationDescription' => $deckRegistrationDescription,
                            'showDeckRegistrationModal' => $showDeckRegistrationModal,
                            'oldDeckPlayerId' => $oldDeckPlayerId,
                        ])
                    @endif
                @else
                    <div class="flex h-full items-center justify-center border border-slate-800/80 bg-slate-950/55 p-6">
                        <div class="text-center">
                            <p class="type-title text-lg text-slate-100">No active event</p>
                            <p class="type-body mt-2 text-sm text-slate-400">Open the Events panel and set an event as active to unlock the workspace.</p>
                            <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label mt-4 inline-flex border border-amber-500/70 bg-amber-500/10 px-3 py-2 text-[10px] text-amber-100 transition hover:bg-amber-500/20">Go to Events</a>
                        </div>
                    </div>
                @endif
            </div>

            @else
            <div class="grid h-full gap-3 xl:grid-cols-[minmax(0,1fr)_16.5rem]">
                <article class="min-h-0 overflow-y-auto no-scrollbar border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.22)_0%,rgba(2,6,23,0.92)_100%)] p-4">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="type-kicker text-[10px] text-cyan-300/75">Leaderboard</p>
                            <h3 class="type-headline mt-1 text-xl text-cyan-100">Players</h3>
                        </div>
                        <span class="type-label text-[10px] text-slate-500">{{ $leaderboard->count() }} total</span>
                    </div>

                    <div class="mt-4 overflow-x-auto">
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
                                                    data-leaderboard-profile-template-id="dashboard-player-profile-template-{{ $row->player_id }}"
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
                </article>

                <div class="grid min-h-0 content-start gap-3">
                    <article class="border border-fuchsia-300/35 bg-[linear-gradient(160deg,rgba(112,26,117,0.16)_0%,rgba(2,6,23,0.92)_100%)] p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="type-title text-sm text-fuchsia-100">Register Player</p>
                            @if ($playerRegistrationEvent)
                                <span class="type-label text-[10px] text-slate-500">{{ $registerableUsers->count() }} available</span>
                            @endif
                        </div>
                        @if ($playerRegistrationEvent)
                            <p class="mt-2 text-sm text-slate-300">Open registration for {{ $playerRegistrationEvent->title }}.</p>
                            <p class="mt-1 text-[11px] text-slate-500">
                                {{ $playerRegistrationEvent->date->format('d M Y') }}
                                @if ($playerRegistrationEvent->eventType)
                                    - {{ $playerRegistrationEvent->eventType->name }}
                                @endif
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($playerRegistrationEvent->usesLockedDecks())
                                    <a href="{{ route('dashboard', ['panel' => 'workspace']) }}" class="type-label border border-amber-400/70 bg-amber-400/12 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                        Open Workspace
                                    </a>
                                @else
                                    <button type="button" data-register-modal-open class="type-label border border-amber-400/70 bg-amber-400/12 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                        Register Player
                                    </button>
                                @endif
                            </div>
                        @else
                            <p class="type-body mt-3 text-sm text-slate-400">No upcoming event is currently open for player registration.</p>
                            <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="type-label mt-3 inline-flex border border-slate-700 px-3 py-1.5 text-[9px] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">
                                Go To Events
                            </a>
                        @endif
                    </article>
                </div>
            </div>

            @if ($playerRegistrationEvent && ! $playerRegistrationEvent->usesLockedDecks())
                @include('dashboard.partials.register-participants-modal', [
                    'registrationEvent' => $playerRegistrationEvent,
                    'registrationPanel' => 'players',
                    'showRegistrationModal' => $showPlayersRegisterModal,
                ])
            @endif

            @if ($leaderboardProfiles->isNotEmpty())
                @foreach ($leaderboard as $row)
                    @php
                        $preview = $leaderboardProfiles->get($row->player_id);
                    @endphp
                    @if ($preview)
                        <template id="dashboard-player-profile-template-{{ $row->player_id }}">
                            @include('home.partials.leaderboard-profile-modal-body', ['preview' => $preview])
                        </template>
                    @endif
                @endforeach

                <div data-leaderboard-profile-modal class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/75 p-4">
                    <div class="max-h-[88vh] w-full max-w-2xl overflow-y-auto border border-cyan-400/50 bg-slate-950 p-4 shadow-[0_28px_70px_rgba(2,6,23,0.82)] sm:p-5">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="type-kicker text-xs text-cyan-300">Player Preview</p>
                                <p class="mt-1 text-[11px] text-slate-500">Quick profile view from the leaderboard.</p>
                            </div>
                            <button type="button" data-leaderboard-profile-close class="inline-flex h-9 w-9 items-center justify-center border border-slate-700 text-slate-300 transition hover:border-rose-500 hover:text-rose-200">
                                x
                            </button>
                        </div>

                        <div data-leaderboard-profile-body></div>
                    </div>
                </div>
            @endif
            @endif
            </section>
        </section>
    </div>

    <div data-event-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
        <div class="max-h-[92vh] w-full max-w-[96rem] overflow-y-auto border border-cyan-400/60 bg-slate-950 p-4 shadow-[0_24px_64px_rgba(2,6,23,0.72)] sm:p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="type-kicker text-xs text-cyan-300">Event View</p>
                    <p class="mt-1 text-[11px] text-slate-500">Read-only workspace preview for this event.</p>
                </div>
                <button type="button" data-event-modal-close class="inline-flex h-9 w-9 items-center justify-center border border-slate-700 text-slate-300 transition hover:border-rose-500 hover:text-rose-200">
                    x
                </button>
            </div>

            <div data-event-modal-body></div>
        </div>
    </div>
</x-layouts.app>
