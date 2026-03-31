<x-layouts.app :title="$event->title . ' | BBXLU'">
    @php
        $bracketLabel = $event->bracket_type === 'swiss_single_elim' ? 'Swiss + Single Elimination' : 'Single Elimination';
        $swissRoundOne = $rounds->first(fn ($round) => $round->stage === 'swiss' && (int) $round->round_number === 1);
        $canReshuffleSwissRoundOne = $event->usesSwissBracket()
            && (! $swissRoundOne || ($swissRoundOne->matches->isNotEmpty() && $swissRoundOne->matches->every(fn ($match) => $match->status === 'pending')));
    @endphp

    <section class="mb-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs uppercase tracking-widest text-slate-400">{{ $event->status }} / {{ $event->bracket_status }}</p>
                <h2 class="mt-2 text-2xl font-bold text-amber-100">{{ $event->title }}</h2>
                <p class="mt-2 text-sm text-slate-300">
                    {{ $event->date->format('d M Y') }} - {{ $event->eventType->name }} - by {{ $event->creator->nickname }}
                </p>
                <p class="mt-1 text-sm text-slate-400">
                    {{ $bracketLabel }}
                    @if ($event->usesSwissBracket())
                        - {{ $event->swiss_rounds }} Swiss rounds - Top {{ $event->top_cut_size }}
                    @endif
                </p>
                @if ($event->location)
                    <p class="mt-1 text-sm text-slate-400">Location: {{ $event->location }}</p>
                @endif
                @if ($event->description)
                    <p class="mt-3 max-w-3xl text-sm text-slate-300">{{ $event->description }}</p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('events.bracket.generate', $event) }}" method="POST">
                    @csrf
                    <button class="rounded-lg border border-emerald-500/60 px-3 py-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/10">
                        Generate Next Round
                    </button>
                </form>
                @if ($canReshuffleSwissRoundOne)
                    <form action="{{ route('events.bracket.generate', $event) }}" method="POST">
                        @csrf
                        <input type="hidden" name="reshuffle" value="1">
                        <button class="rounded-lg border border-cyan-500/60 px-3 py-2 text-sm font-semibold text-cyan-100 hover:bg-cyan-500/10">
                            Reshuffle Round 1
                        </button>
                    </form>
                @endif
                <a href="{{ route('events.index') }}" class="rounded-lg border border-slate-700 px-3 py-2 text-sm hover:border-amber-500 hover:text-amber-200">Back to events</a>
                <a href="{{ route('events.edit', $event) }}" class="rounded-lg border border-slate-700 px-3 py-2 text-sm hover:border-amber-500 hover:text-amber-200">Edit event</a>
                <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Delete this event and all related records?');">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg border border-rose-500/60 px-3 py-2 text-sm text-rose-200 hover:bg-rose-500/10">
                        Delete event
                    </button>
                </form>
            </div>
        </div>
    </section>

    @if ($event->usesSwissBracket() && $swissStandings->isNotEmpty())
        <section class="mb-8 rounded-xl border border-cyan-400/25 bg-slate-900/70 p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-cyan-300/75">Swiss</p>
                    <h3 class="mt-1 text-lg font-semibold text-cyan-100">Current Standings</h3>
                </div>
                <span class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ $swissStandings->count() }} players</span>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400">
                            <th class="px-3 py-2">Rank</th>
                            <th class="px-3 py-2">Participant</th>
                            <th class="px-3 py-2">W-L</th>
                            <th class="px-3 py-2">Byes</th>
                            <th class="px-3 py-2">Score</th>
                            <th class="px-3 py-2">Finish Diff</th>
                            <th class="px-3 py-2">Finish Pts</th>
                            <th class="px-3 py-2">Buchholz</th>
                            <th class="px-3 py-2">History</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($swissStandings as $row)
                            <tr class="border-b border-slate-900">
                                <td class="px-3 py-2 text-amber-200">#{{ $row['rank'] }}</td>
                                <td class="px-3 py-2 font-medium text-slate-100">{{ $row['player']->user->nickname }}</td>
                                <td class="px-3 py-2 text-slate-300">{{ $row['wins'] }}-{{ $row['losses'] }}</td>
                                <td class="px-3 py-2 text-slate-300">{{ $row['byes'] }}</td>
                                <td class="px-3 py-2 text-slate-300">{{ number_format($row['match_points'], 1) }}</td>
                                <td class="px-3 py-2 text-slate-300">{{ $row['points_diff'] }}</td>
                                <td class="px-3 py-2 text-slate-300">{{ $row['battle_points'] }}</td>
                                <td class="px-3 py-2 text-slate-300">{{ number_format($row['buchholz'], 1) }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($row['history'] as $historyItem)
                                            <span class="rounded px-2 py-1 text-[11px] font-semibold {{ $historyItem === 'W' ? 'bg-cyan-500/20 text-cyan-200' : ($historyItem === 'L' ? 'bg-rose-500/20 text-rose-200' : 'bg-amber-500/20 text-amber-200') }}">
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

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_22rem]">
        <div class="space-y-6">
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-amber-300/75">Bracket</p>
                        <h3 class="mt-1 text-lg font-semibold text-amber-100">Rounds And Matches</h3>
                        @if ($event->usesSwissBracket())
                            <p class="mt-1 text-xs text-slate-500">Round 1 can be reshuffled before results start. Later Swiss rounds are auto-generated from completed standings.</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form action="{{ route('events.bracket.generate', $event) }}" method="POST">
                            @csrf
                            <button class="rounded-lg border border-emerald-500/60 px-3 py-2 text-xs font-semibold uppercase tracking-[0.14em] text-emerald-200 hover:bg-emerald-500/10">
                                Generate Next Round
                            </button>
                        </form>
                        @if ($canReshuffleSwissRoundOne)
                            <form action="{{ route('events.bracket.generate', $event) }}" method="POST">
                                @csrf
                                <input type="hidden" name="reshuffle" value="1">
                                <button class="rounded-lg border border-cyan-500/60 px-3 py-2 text-xs font-semibold uppercase tracking-[0.14em] text-cyan-100 hover:bg-cyan-500/10">
                                    Reshuffle Round 1
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    @forelse ($rounds as $round)
                        <section class="rounded-xl border border-slate-800 bg-slate-950/45 p-4">
                            <div class="flex items-center justify-between gap-3 border-b border-slate-800 pb-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-100">{{ $round->label ?: ucfirst(str_replace('_', ' ', $round->stage)).' Round '.$round->round_number }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.16em] text-slate-500">{{ str_replace('_', ' ', $round->stage) }} - {{ $round->status }}</p>
                                </div>
                                <span class="rounded-full border border-slate-700 px-3 py-1 text-xs text-slate-300">{{ $round->matches->count() }} matches</span>
                            </div>

                            <div class="mt-4 space-y-4">
                                @foreach ($round->matches->sortBy('match_number') as $match)
                                    <article class="rounded-xl border border-slate-800/90 bg-slate-950/70 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-slate-100">
                                                    Match {{ $match->match_number ?: $loop->iteration }}:
                                                    {{ $match->player1->user->nickname }}
                                                    @if ($match->player2)
                                                        vs {{ $match->player2->user->nickname }}
                                                    @else
                                                        vs BYE
                                                    @endif
                                                </p>
                                                <p class="mt-1 text-xs uppercase tracking-[0.14em] text-slate-500">
                                                    {{ $match->status }}
                                                    @if ($match->is_bye)
                                                        - auto bye
                                                    @endif
                                                </p>
                                            </div>
                                            <form action="{{ route('events.matches.destroy', [$event, $match]) }}" method="POST" onsubmit="return confirm('Delete this match?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-md border border-rose-500/60 px-2 py-1 text-xs text-rose-200 hover:bg-rose-500/10">Delete</button>
                                            </form>
                                        </div>

                                        @if ($match->is_bye)
                                            <p class="mt-3 text-sm text-emerald-200">
                                                {{ $match->winner?->user->nickname ?: $match->player1->user->nickname }} advances with a bye.
                                            </p>
                                        @elseif ($match->status === 'completed')
                                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                <div class="rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2">
                                                    <p class="text-xs uppercase tracking-[0.14em] text-slate-500">Result</p>
                                                    <p class="mt-1 text-sm text-slate-100">
                                                        {{ $match->player1->user->nickname }} {{ $match->player1_score }} - {{ $match->player2_score }} {{ $match->player2?->user->nickname }}
                                                    </p>
                                                    @if ($match->winner)
                                                        <p class="mt-1 text-xs text-emerald-300">Winner: {{ $match->winner->user->nickname }}</p>
                                                    @endif
                                                </div>
                                                <div class="rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2">
                                                    <p class="text-xs uppercase tracking-[0.14em] text-slate-500">Battle History</p>
                                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                                        @foreach ($match->battleResults() as $battleResult)
                                                            <span class="rounded px-2 py-1 text-[11px] font-semibold {{ $battleResult['winner'] === 1 ? 'bg-cyan-500/20 text-cyan-200' : 'bg-rose-500/20 text-rose-200' }}">
                                                                B{{ $battleResult['slot'] }}:
                                                                {{ $battleResult['winner'] === 1 ? 'P1' : 'P2' }}
                                                                @if ($battleResult['type'])
                                                                    - {{ ucfirst($battleResult['type']) }}
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                <div class="rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2">
                                                    <p class="text-xs uppercase tracking-[0.14em] text-slate-500">{{ $match->player1->user->nickname }} Bey Picks</p>
                                                    <p class="mt-1 text-sm text-slate-300">{{ collect([$match->player1_bey1, $match->player1_bey2, $match->player1_bey3])->filter()->implode(', ') ?: 'Not recorded' }}</p>
                                                </div>
                                                <div class="rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2">
                                                    <p class="text-xs uppercase tracking-[0.14em] text-slate-500">{{ $match->player2?->user->nickname }} Bey Picks</p>
                                                    <p class="mt-1 text-sm text-slate-300">{{ collect([$match->player2_bey1, $match->player2_bey2, $match->player2_bey3])->filter()->implode(', ') ?: 'Not recorded' }}</p>
                                                </div>
                                            </div>
                                        @else
                                            @php
                                                $matchWinThreshold = $event->battleWinThresholdForMatch($match, $round, $round->stage, $round->matches->count());
                                                $battleSlotCount = $event->maxBattleSlotsForThreshold($matchWinThreshold);
                                                $player1SelectedSide = in_array($match->player1StadiumSide?->code, ['X', 'B'], true) ? $match->player1StadiumSide?->code : null;
                                                $player2SelectedSide = in_array($match->player2StadiumSide?->code, ['X', 'B'], true) ? $match->player2StadiumSide?->code : null;
                                            @endphp
                                            <form action="{{ route('events.matches.store', $event) }}" method="POST" class="mt-4 grid gap-4">
                                                @csrf
                                                <input type="hidden" name="match_id" value="{{ $match->id }}">
                                                <input type="hidden" name="event_round_id" value="{{ $round->id }}">
                                                <input type="hidden" name="stage" value="{{ $round->stage }}">
                                                <input type="hidden" name="player1_id" value="{{ $match->player1_id }}">
                                                <input type="hidden" name="player2_id" value="{{ $match->player2_id }}">
                                                <input type="hidden" name="round_number" value="{{ $round->round_number }}">
                                                <input type="hidden" name="match_number" value="{{ $match->match_number }}">

                                                <div class="grid gap-4 sm:grid-cols-2">
                                                    <div class="grid gap-2">
                                                        <p class="text-sm font-semibold text-slate-100">{{ $match->player1->user->nickname }} Bey Picks</p>
                                                        @foreach ([1, 2, 3] as $slot)
                                                            <label class="grid gap-1">
                                                                <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Bey {{ $slot }}</span>
                                                                <input
                                                                    name="player1_bey{{ $slot }}"
                                                                    value="{{ $match->{'player1_bey'.$slot} }}"
                                                                    class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                                                                >
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    <div class="grid gap-2">
                                                        <p class="text-sm font-semibold text-slate-100">{{ $match->player2?->user->nickname }} Bey Picks</p>
                                                        @foreach ([1, 2, 3] as $slot)
                                                            <label class="grid gap-1">
                                                                <span class="text-xs uppercase tracking-[0.14em] text-slate-500">Bey {{ $slot }}</span>
                                                                <input
                                                                    name="player2_bey{{ $slot }}"
                                                                    value="{{ $match->{'player2_bey'.$slot} }}"
                                                                    class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                                                                >
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <div class="grid gap-3 sm:grid-cols-2" data-stadium-side-control>
                                                    @foreach ([1 => ['label' => $match->player1->user->nickname, 'selected' => $player1SelectedSide], 2 => ['label' => $match->player2?->user->nickname, 'selected' => $player2SelectedSide]] as $slot => $sideInfo)
                                                        <div class="grid gap-1.5" data-stadium-side-group data-player-slot="{{ $slot }}">
                                                            <div class="flex items-center justify-between gap-2">
                                                                <span class="text-xs uppercase tracking-[0.14em] text-slate-500">{{ $sideInfo['label'] }} Side</span>
                                                                <span class="text-[10px] uppercase tracking-[0.14em] text-slate-600">Auto-opposite</span>
                                                            </div>
                                                            <input type="hidden" name="player{{ $slot }}_stadium_side" value="{{ $sideInfo['selected'] ?? '' }}" data-stadium-side-input>
                                                            <div class="grid grid-cols-2 gap-2">
                                                                @foreach (['X', 'B'] as $sideCode)
                                                                    @php
                                                                        $isSelected = $sideInfo['selected'] === $sideCode;
                                                                    @endphp
                                                                    <button
                                                                        type="button"
                                                                        data-stadium-side-choice
                                                                        data-side-choice="{{ $sideCode }}"
                                                                        class="rounded-lg border px-3 py-2 text-xs font-semibold uppercase tracking-[0.16em] transition {{ $isSelected ? 'border-cyan-400/70 bg-cyan-400/10 text-cyan-100' : 'border-slate-700 bg-slate-950/70 text-slate-300 hover:border-cyan-400/45 hover:text-cyan-100' }}"
                                                                    >
                                                                        {{ $sideCode }}
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div>
                                                    <p class="text-sm font-semibold text-slate-100">Battle Results</p>
                                                    <p class="mt-1 text-xs text-slate-500">First to {{ $matchWinThreshold }} points. Spin = 1, Burst = 2, Over = 2, Extreme = 3.</p>
                                                    <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                                                        @foreach (range(1, $battleSlotCount) as $slot)
                                                            @include('partials.battle-result-picker', [
                                                                'slot' => $slot,
                                                                'player1Name' => $match->player1->user->nickname,
                                                                'player2Name' => $match->player2?->user->nickname ?? 'P2',
                                                                'selectedWinner' => $match->{'result_'.$slot},
                                                                'selectedType' => $match->{'result_type_'.$slot},
                                                            ])
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <button class="w-fit rounded-lg border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-100 hover:bg-amber-500/20">
                                                    Save Match Result
                                                </button>
                                            </form>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @empty
                        <p class="text-sm text-slate-400">No rounds generated yet. Add participants, then generate the first round.</p>
                    @endforelse
                </div>
            </article>
        </div>

        <div class="space-y-6">
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
                <h3 class="text-lg font-semibold text-amber-100">Add Participant</h3>
                <p class="mt-1 text-xs text-slate-400">Unknown nicknames still auto-create user + player accounts.</p>
                <form action="{{ route('events.participants.store', $event) }}" method="POST" class="mt-4 grid gap-3">
                    @csrf
                    <label class="grid gap-1">
                        <span class="text-sm text-slate-300">Nickname</span>
                        <input name="nickname" value="{{ old('nickname') }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                    </label>
                    <button class="w-fit rounded-lg border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-100 hover:bg-amber-500/20">Add Participant</button>
                </form>
            </article>

            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
                <h3 class="mb-3 text-lg font-semibold text-amber-100">Participants</h3>
                <div class="space-y-2">
                    @forelse ($participants as $participant)
                        <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-800 bg-slate-950/60 px-3 py-2 text-sm">
                            <div>
                                {{ $participant->user->nickname }}
                                @if (! $participant->user->is_claimed)
                                    <span class="ml-2 rounded border border-amber-500/60 px-2 py-0.5 text-xs text-amber-300">Auto</span>
                                @endif
                            </div>
                            <form action="{{ route('events.participants.destroy', [$event, $participant]) }}" method="POST" onsubmit="return confirm('Remove this participant from the event?');">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-md border border-rose-500/60 px-2 py-1 text-xs text-rose-200 hover:bg-rose-500/10">Remove</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No participants yet.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
                <h3 class="text-lg font-semibold text-amber-100">Final Placements</h3>
                <p class="mt-1 text-xs text-slate-400">Placements are generated automatically when the elimination bracket finishes.</p>

                <div class="mt-4 space-y-2">
                    @forelse ($results as $result)
                        <div class="flex items-center gap-2 rounded-lg border border-slate-800 bg-slate-950/60 px-3 py-2 text-sm">
                            <span class="inline-flex min-w-8 justify-center rounded border border-amber-500/40 px-2 py-1 text-xs font-semibold text-amber-200">#{{ $result->placement }}</span>
                            <span>{{ $result->player->user->nickname }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">
                            {{ $event->bracket_status === 'completed' ? 'No placements were generated.' : 'Placements will appear automatically after the final match.' }}
                        </p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
                <h3 class="text-lg font-semibold text-amber-100">Auto Awards</h3>
                <p class="mt-1 text-xs text-slate-400">Awards are assigned from the finished bracket outcome and Swiss standings.</p>

                <div class="mt-4 space-y-2">
                    @forelse ($eventAwards as $eventAward)
                        <div class="flex items-center gap-2 rounded-lg border border-slate-800 bg-slate-950/60 px-3 py-2 text-sm">
                            <span class="inline-flex rounded border border-cyan-500/40 px-2 py-1 text-xs font-semibold text-cyan-200">{{ $eventAward->award->name }}</span>
                            <span>{{ $eventAward->player->user->nickname }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">
                            {{ $event->bracket_status === 'completed' ? 'No auto awards were generated.' : 'Awards will appear automatically when the event concludes.' }}
                        </p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
</x-layouts.app>
