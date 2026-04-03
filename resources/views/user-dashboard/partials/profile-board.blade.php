@php
    $displayUser = $profileUser ?? $viewer;
    $displayName = $displayUser?->nickname ?? 'Unknown player';
    $profileName = $displayUser?->name ?: $displayName;
    $claimLabel = $displayUser?->is_claimed ? 'Claimed Account' : 'Unclaimed Account';
    $profileModeLabel = $isSelfView ? 'Self Dashboard' : 'Player Profile';
    $recentMatchEventOptions = $recentMatches
        ->map(fn ($match) => [
            'id' => (string) $match->event_id,
            'title' => $match->event->title,
        ])
        ->unique('id')
        ->values();
    $statGroups = [
        [
            'eyebrow' => 'Competitive Snapshot',
            'title' => 'Event Performance',
            'accent' => 'cyan',
            'cards' => [
                ['label' => 'Wins', 'value' => $profileStats['wins'], 'tone' => 'text-amber-100'],
                ['label' => 'Top 4', 'value' => $profileStats['top4'], 'tone' => 'text-cyan-100'],
                ['label' => 'Awards', 'value' => $profileStats['awards'], 'tone' => 'text-emerald-100'],
                ['label' => 'Match Record', 'value' => $profileStats['match_record'], 'tone' => 'text-slate-100'],
                ['label' => 'Win Rate %', 'value' => $profileStats['win_rate'] !== null ? number_format($profileStats['win_rate'], 1).'%' : '-', 'tone' => 'text-cyan-100'],
                ['label' => 'Top Cut %', 'value' => $profileStats['top_cut_rate'] !== null ? number_format($profileStats['top_cut_rate'], 1).'%' : '-', 'tone' => 'text-amber-100'],
            ],
        ],
        [
            'eyebrow' => 'Battle Profile',
            'title' => 'Scoring And Sides',
            'accent' => 'emerald',
            'cards' => [
                ['label' => 'Avg Score', 'value' => $profileStats['avg_score'] !== null ? number_format($profileStats['avg_score'], 1) : '-', 'tone' => 'text-slate-100'],
                ['label' => 'Avg Against', 'value' => $profileStats['avg_against'] !== null ? number_format($profileStats['avg_against'], 1) : '-', 'tone' => 'text-slate-100'],
                ['label' => 'Battle Pts', 'value' => $profileStats['battle_points'], 'tone' => 'text-cyan-100'],
                ['label' => 'Byes', 'value' => $profileStats['byes'], 'tone' => 'text-emerald-100'],
                ['label' => 'X Side Win %', 'value' => $profileStats['x_side_win_rate'] !== null ? number_format($profileStats['x_side_win_rate'], 1).'%' : '-', 'tone' => 'text-slate-100', 'meta' => $profileStats['x_side_record']],
                ['label' => 'B Side Win %', 'value' => $profileStats['b_side_win_rate'] !== null ? number_format($profileStats['b_side_win_rate'], 1).'%' : '-', 'tone' => 'text-slate-100', 'meta' => $profileStats['b_side_record']],
            ],
        ],
        [
            'eyebrow' => 'Finish Profile',
            'title' => 'Bey And Finish Trends',
            'accent' => 'fuchsia',
            'cards' => [
                ['label' => 'Most Used Bey', 'value' => $profileStats['most_used_bey'] ?: '-', 'tone' => 'text-fuchsia-100', 'meta' => $profileStats['most_used_bey_count'] > 0 ? $profileStats['most_used_bey_count'].' uses' : null],
                ['label' => 'Best Finish', 'value' => $profileStats['best_finish'] ? \Illuminate\Support\Str::headline($profileStats['best_finish']) : '-', 'tone' => 'text-fuchsia-100'],
                ['label' => 'Spin %', 'value' => $profileStats['finish_percentages']['spin'] !== null ? number_format($profileStats['finish_percentages']['spin'], 1).'%' : '-', 'tone' => 'text-slate-100'],
                ['label' => 'Burst %', 'value' => $profileStats['finish_percentages']['burst'] !== null ? number_format($profileStats['finish_percentages']['burst'], 1).'%' : '-', 'tone' => 'text-slate-100'],
                ['label' => 'Over %', 'value' => $profileStats['finish_percentages']['over'] !== null ? number_format($profileStats['finish_percentages']['over'], 1).'%' : '-', 'tone' => 'text-slate-100'],
                ['label' => 'Extreme %', 'value' => $profileStats['finish_percentages']['extreme'] !== null ? number_format($profileStats['finish_percentages']['extreme'], 1).'%' : '-', 'tone' => 'text-slate-100'],
            ],
        ],
    ];
@endphp

<div class="grid gap-4 2xl:grid-cols-[minmax(0,1.65fr)_minmax(22rem,0.9fr)]">
    <section class="space-y-4">
        <article class="overflow-hidden border border-cyan-400/30 bg-[linear-gradient(145deg,rgba(8,47,73,0.82)_0%,rgba(2,6,23,0.95)_50%,rgba(15,23,42,0.98)_100%)] shadow-[0_24px_60px_rgba(2,6,23,0.42)]">
            <div class="grid gap-4 p-4 lg:grid-cols-[minmax(0,1.3fr)_minmax(18rem,1fr)] lg:p-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="type-label border border-cyan-400/45 bg-cyan-400/10 px-2.5 py-1 text-[10px] text-cyan-100">{{ strtoupper($profileModeLabel) }}</span>
                        @if ($profilePlayer)
                            <span class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-300">Player #{{ $profilePlayer->id }}</span>
                        @endif
                        <span class="type-label border border-emerald-400/40 bg-emerald-500/10 px-2.5 py-1 text-[10px] text-emerald-200">{{ $claimLabel }}</span>
                    </div>

                    <h2 class="type-headline mt-3 break-words text-3xl text-cyan-100 sm:text-4xl">{{ $displayName }}</h2>
                    <p class="mt-2 text-sm text-slate-300">{{ $profileName }}</p>

                    @if ($isSelfView)
                        <p class="mt-2 text-sm text-slate-400">{{ $displayUser?->email ?: 'No email saved yet.' }}</p>
                    @else
                        <p class="mt-2 text-sm text-slate-400">Public player profile.</p>
                    @endif
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <button
                        type="button"
                        data-profile-events-open
                        class="border border-amber-400/25 bg-slate-950/45 px-4 py-3 text-left transition hover:border-amber-300/50 hover:bg-slate-950/65"
                    >
                        <p class="type-kicker text-[10px] text-amber-300/75">Events Joined</p>
                        <p class="mt-2 text-3xl font-bold text-amber-100">{{ $profileStats['joined'] }}</p>
                        <p class="mt-1 text-xs text-slate-400">Open full event list</p>
                    </button>

                    <div class="border border-slate-800 bg-slate-950/45 px-4 py-3">
                        <p class="type-kicker text-[10px] text-slate-500">Swiss Events</p>
                        <p class="mt-2 text-3xl font-bold text-slate-100">{{ $profileStats['swiss_events'] }}</p>
                        <p class="mt-1 text-xs text-slate-400">Used for top cut rate</p>
                    </div>

                    <div class="border border-fuchsia-400/25 bg-slate-950/45 px-4 py-3">
                        <p class="type-kicker text-[10px] text-fuchsia-300/75">Public Link</p>
                        <p class="mt-2 truncate text-sm font-semibold text-slate-100">{{ $publicProfileUrl ?: '-' }}</p>
                        @if ($publicProfileUrl && ! $isSelfView)
                            <a href="{{ $publicProfileUrl }}" class="mt-2 inline-flex border border-fuchsia-400/40 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-fuchsia-100 transition hover:bg-fuchsia-400/10">Open</a>
                        @elseif ($publicProfileUrl)
                            <p class="mt-2 text-[11px] text-slate-400">Use this as the shareable profile URL.</p>
                        @endif
                    </div>

                    <div class="border border-cyan-400/25 bg-slate-950/45 px-4 py-3">
                        <p class="type-kicker text-[10px] text-cyan-300/75">{{ $isSelfView ? 'Self View' : 'Navigation' }}</p>
                        @if ($isSelfView)
                            <a href="{{ route('home') }}" class="mt-2 inline-flex border border-cyan-400/40 px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-cyan-100 transition hover:bg-cyan-400/10">Home</a>
                        @elseif ($viewer && $viewer->role !== 'admin')
                            <a href="{{ $selfDashboardUrl }}" class="mt-2 inline-flex border border-cyan-400/40 px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-cyan-100 transition hover:bg-cyan-400/10">My Dashboard</a>
                        @else
                            <a href="{{ route('home') }}" class="mt-2 inline-flex border border-cyan-400/40 px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-cyan-100 transition hover:bg-cyan-400/10">Home</a>
                        @endif
                    </div>
                </div>
            </div>
        </article>

        <section class="grid gap-4 xl:grid-cols-2 2xl:grid-cols-3">
            @foreach ($statGroups as $group)
                @php
                    $groupBorder = match ($group['accent']) {
                        'emerald' => 'border-emerald-400/20',
                        'fuchsia' => 'border-fuchsia-400/20',
                        default => 'border-cyan-400/20',
                    };
                    $groupEyebrow = match ($group['accent']) {
                        'emerald' => 'text-emerald-300/75',
                        'fuchsia' => 'text-fuchsia-300/75',
                        default => 'text-cyan-300/75',
                    };
                    $groupTitle = match ($group['accent']) {
                        'emerald' => 'text-emerald-100',
                        'fuchsia' => 'text-fuchsia-100',
                        default => 'text-cyan-100',
                    };
                @endphp
                <article class="border {{ $groupBorder }} bg-slate-950/72 p-4">
                    <div class="border-b border-slate-800/80 pb-3">
                        <p class="type-kicker text-[10px] {{ $groupEyebrow }}">{{ $group['eyebrow'] }}</p>
                        <h3 class="type-headline mt-1 text-lg {{ $groupTitle }}">{{ $group['title'] }}</h3>
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach ($group['cards'] as $card)
                            <article class="border border-slate-800/80 bg-slate-950/78 px-4 py-3">
                                <p class="type-kicker text-[10px] text-slate-500">{{ $card['label'] }}</p>
                                <p class="mt-2 break-words text-xl font-bold {{ $card['tone'] }}">{{ $card['value'] }}</p>
                                @if (! empty($card['meta']))
                                    <p class="mt-1 text-[11px] text-slate-500">{{ $card['meta'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
            <article class="border border-cyan-400/20 bg-slate-950/72 p-4">
                <div class="flex flex-wrap items-end justify-between gap-3 border-b border-slate-800/80 pb-3">
                    <div>
                        <p class="type-kicker text-[10px] text-cyan-300/75">Match Log</p>
                        <h3 class="type-headline mt-1 text-lg text-cyan-100">Recent Matches</h3>
                    </div>
                    @if ($recentMatchEventOptions->isNotEmpty())
                        <label class="block min-w-[11rem]">
                            <span class="type-kicker block text-[10px] text-slate-500">Select Event</span>
                            <select
                                data-profile-match-filter
                                class="mt-2 w-full border border-slate-700 bg-slate-950/75 px-3 py-2 text-sm text-slate-100 outline-none transition focus:border-cyan-400/50"
                            >
                                <option value="">All Events</option>
                                @foreach ($recentMatchEventOptions as $eventOption)
                                    <option value="{{ $eventOption['id'] }}">{{ $eventOption['title'] }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                </div>

                <div class="mt-3 max-h-[34rem] space-y-2 overflow-y-auto pr-1" data-profile-match-list>
                    @forelse ($recentMatches as $match)
                        @php
                            $isPlayerOne = $match->player1_id === $profilePlayer?->id;
                            $opponentName = $match->is_bye
                                ? 'BYE'
                                : ($isPlayerOne ? ($match->player2?->user?->nickname ?? '- opponent') : ($match->player1?->user?->nickname ?? 'Unknown'));
                            $playerScore = $isPlayerOne ? $match->player1_score : $match->player2_score;
                            $opponentScore = $isPlayerOne ? $match->player2_score : $match->player1_score;
                            $outcomeLabel = $match->status !== 'completed'
                                ? 'Pending'
                                : ($match->winner_id === $profilePlayer?->id ? 'W' : 'L');
                            $outcomeClasses = $outcomeLabel === 'W'
                                ? 'border-emerald-500/35 bg-emerald-500/10 text-emerald-200'
                                : ($outcomeLabel === 'L'
                                    ? 'border-rose-500/35 bg-rose-500/10 text-rose-200'
                                    : 'border-amber-500/35 bg-amber-500/10 text-amber-200');
                            $roundLabel = $match->round?->label ?: ucfirst(str_replace('_', ' ', $match->stage)).' Round '.($match->round_number ?? 1);
                        @endphp
                        <button
                            type="button"
                            data-profile-match-open
                            data-profile-match-template-id="profile-match-template-{{ $match->id }}"
                            data-profile-match-event-id="{{ $match->event_id }}"
                            class="group block w-full cursor-pointer border border-slate-800/80 bg-slate-900/65 px-3 py-2.5 text-left transition duration-200 hover:-translate-y-0.5 hover:border-cyan-400/45 hover:bg-slate-900/80 hover:shadow-[0_14px_28px_rgba(14,165,233,0.12)]"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-100 transition group-hover:text-cyan-50">vs {{ $opponentName }}</p>
                                    <p class="mt-1 text-[11px] uppercase tracking-[0.14em] text-slate-500">
                                        {{ $match->event->title }} / {{ $roundLabel }}
                                    </p>
                                </div>
                                <span class="type-label border px-2 py-1 text-[9px] {{ $outcomeClasses }}">{{ $outcomeLabel }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3">
                                <p class="text-sm text-slate-300">{{ $playerScore }} - {{ $opponentScore }}</p>
                                <span class="type-label text-[10px] text-cyan-200/75 transition group-hover:text-cyan-100">Details</span>
                            </div>
                        </button>
                    @empty
                        <p class="text-sm text-slate-400">No recorded matches yet.</p>
                    @endforelse
                    @if ($recentMatches->isNotEmpty())
                        <p class="hidden text-sm text-slate-400" data-profile-match-filter-empty>No matches for the selected event.</p>
                    @endif
                </div>
            </article>

            <article class="border border-amber-400/20 bg-slate-950/72 p-4">
                <div class="border-b border-slate-800/80 pb-2">
                    <p class="type-kicker text-[10px] text-amber-300/75">Results</p>
                    <h3 class="type-headline mt-1 text-lg text-amber-100">Recent Placements</h3>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse ($recentResults as $result)
                        <article class="flex items-center justify-between gap-3 border border-slate-800/80 bg-slate-900/65 px-3 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-100">{{ $result->event->title }}</p>
                                <p class="mt-1 text-[11px] uppercase tracking-[0.14em] text-slate-500">
                                    {{ $result->event->date->format('d M Y') }}@if ($result->event->eventType) / {{ $result->event->eventType->name }}@endif
                                </p>
                            </div>
                            <span class="text-lg font-bold text-amber-200">#{{ $result->placement }}</span>
                        </article>
                    @empty
                        <p class="text-sm text-slate-400">No recorded results yet.</p>
                    @endforelse
                </div>
            </article>
        </section>
    </section>

    <aside class="space-y-4 2xl:pt-0">
        <article class="border border-slate-800/80 bg-slate-950/72 p-4">
            <div class="border-b border-slate-800/80 pb-2">
                <p class="type-kicker text-[10px] text-emerald-300/75">Schedule</p>
                <h3 class="type-headline mt-1 text-lg text-emerald-100">Upcoming Events</h3>
            </div>

            <div class="mt-3 space-y-2">
                @forelse ($upcomingEvents as $participant)
                    <article class="border border-slate-800/80 bg-slate-900/65 px-3 py-2.5">
                        <p class="text-sm font-semibold text-slate-100">{{ $participant->event->title }}</p>
                        <p class="mt-1 text-[11px] uppercase tracking-[0.14em] text-slate-500">
                            {{ $participant->event->date->format('d M Y') }}@if ($participant->event->eventType) / {{ $participant->event->eventType->name }}@endif
                        </p>
                        <p class="mt-1 text-xs text-slate-400">{{ $participant->event->location ?: 'Venue to be announced.' }}</p>
                    </article>
                @empty
                    <p class="text-sm text-slate-400">No upcoming registrations right now.</p>
                @endforelse
            </div>
        </article>

        <article class="border border-fuchsia-400/20 bg-slate-950/72 p-4">
            <div class="border-b border-slate-800/80 pb-2">
                <p class="type-kicker text-[10px] text-fuchsia-300/75">Awards</p>
                <h3 class="type-headline mt-1 text-lg text-fuchsia-100">Recent Award Calls</h3>
            </div>

            <div class="mt-3 space-y-2">
                @forelse ($recentAwards as $award)
                    <article class="border border-slate-800/80 bg-slate-900/65 px-3 py-2.5">
                        <p class="text-sm font-semibold text-slate-100">{{ $award->award->name }}</p>
                        <p class="mt-1 text-[11px] uppercase tracking-[0.14em] text-slate-500">
                            {{ $award->event->title }} / {{ $award->event->date->format('d M Y') }}
                        </p>
                    </article>
                @empty
                    <p class="text-sm text-slate-400">No awards assigned yet.</p>
                @endforelse
            </div>
        </article>
    </aside>
</div>

@foreach ($recentMatches as $match)
    <template id="profile-match-template-{{ $match->id }}">
        @include('user-dashboard.partials.profile-match-modal-body', [
            'match' => $match,
            'profilePlayer' => $profilePlayer,
        ])
    </template>
@endforeach

<div class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/82 px-4 py-6" data-profile-events-modal>
    <div class="flex max-h-[88vh] w-full max-w-3xl flex-col border border-slate-700/80 bg-slate-900 shadow-[0_24px_60px_rgba(2,6,23,0.72)]">
        <div class="flex items-center justify-between gap-3 border-b border-slate-800/80 px-4 py-3">
            <div>
                <p class="type-kicker text-[10px] text-amber-300/75">Event History</p>
                <h3 class="type-title mt-1 text-lg text-slate-100">Events Joined</h3>
            </div>
            <button
                type="button"
                data-profile-events-close
                class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-300 transition hover:border-slate-500 hover:text-white"
            >
                Close
            </button>
        </div>
        <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4">
            <div class="space-y-2">
                @forelse ($joinedEvents as $participant)
                    @php
                        $placement = $participant->event->results->firstWhere('player_id', $profilePlayer?->id)?->placement;
                    @endphp
                    <article class="border border-slate-800/80 bg-slate-950/75 px-3 py-2.5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-100">{{ $participant->event->title }}</p>
                                <p class="mt-1 text-[11px] uppercase tracking-[0.14em] text-slate-500">
                                    {{ $participant->event->date->format('d M Y') }}@if ($participant->event->eventType) / {{ $participant->event->eventType->name }}@endif
                                </p>
                            </div>
                            <span class="type-label border border-slate-700 px-2 py-1 text-[9px] text-slate-300">{{ strtoupper($participant->event->status) }}</span>
                        </div>
                        <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-400">
                            <span>{{ $participant->event->location ?: 'Venue TBD' }}</span>
                            <span>{{ $placement ? '#'.$placement : 'No final placement yet' }}</span>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-slate-400">No joined events yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-[71] hidden items-center justify-center bg-slate-950/82 px-4 py-6" data-profile-match-modal>
    <div class="flex max-h-[88vh] w-full max-w-4xl flex-col border border-slate-700/80 bg-slate-900 shadow-[0_24px_60px_rgba(2,6,23,0.72)]">
        <div class="flex items-center justify-between gap-3 border-b border-slate-800/80 px-4 py-3">
            <div>
                <p class="type-kicker text-[10px] text-cyan-300/75">Match History</p>
                <h3 class="type-title mt-1 text-lg text-slate-100">Match View</h3>
            </div>
            <button
                type="button"
                data-profile-match-close
                class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-300 transition hover:border-slate-500 hover:text-white"
            >
                Close
            </button>
        </div>
        <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4" data-profile-match-body></div>
    </div>
</div>
