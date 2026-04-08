@php
    $submitLabel = $submitLabel ?? 'Save Match Result';
@endphp

<form action="{{ route('events.matches.store', $selectedEvent) }}" method="POST" class="grid gap-4">
    @csrf
    <input type="hidden" name="dashboard_redirect" value="1">
    <input type="hidden" name="dashboard_panel" value="workspace">
    <input type="hidden" name="dashboard_event_id" value="{{ $selectedEvent->id }}">
    <input type="hidden" name="match_id" value="{{ $match->id }}">
    <input type="hidden" name="event_round_id" value="{{ $round->id }}">
    <input type="hidden" name="stage" value="{{ $round->stage }}">
    <input type="hidden" name="player1_id" value="{{ $match->player1_id }}">
    <input type="hidden" name="player2_id" value="{{ $match->player2_id }}">
    <input type="hidden" name="round_number" value="{{ $round->round_number }}">
    <input type="hidden" name="match_number" value="{{ $match->match_number }}">

    @if ($matchErrorMessages->isNotEmpty())
        <div class="border border-rose-500/45 bg-rose-500/10 px-3 py-2">
            @foreach ($matchErrorMessages as $message)
                <p class="text-[11px] text-rose-200">{{ $message }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid gap-3.5 xl:grid-cols-[minmax(14.5rem,0.72fr)_minmax(0,1.58fr)] xl:items-start">
        <section class="grid gap-2.5" data-stadium-side-control>
            @foreach ($playerSetupCards as $setupCard)
                @php
                    $selectedSide = in_array($setupCard['selectedSide'], ['X', 'B'], true) ? $setupCard['selectedSide'] : null;
                    $registeredBeys = $setupCard['participant'] ? array_values(array_filter($setupCard['participant']->registeredBeys())) : [];
                @endphp
                <div class="rounded-2xl border border-slate-800/85 bg-slate-950/70 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Player {{ $setupCard['slot'] }}</p>
                            <h4 class="mt-1 truncate text-[13px] font-semibold text-slate-50">{{ $setupCard['title'] }}</h4>
                        </div>
                        @if ($usesRegisteredDecks)
                            <span class="rounded-full border border-cyan-500/35 bg-cyan-500/[0.06] px-2 py-1 text-[9px] font-semibold uppercase tracking-[0.14em] text-cyan-200">Registered</span>
                        @endif
                    </div>

                    @if ($usesRegisteredDecks)
                        <div class="mt-2.5">
                            <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Bey Picks</p>
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                @forelse ($registeredBeys as $bey)
                                    <span class="rounded-full border border-slate-700/80 bg-slate-900/75 px-2 py-0.5 text-[10px] text-slate-200">{{ $bey }}</span>
                                @empty
                                    <span class="text-[11px] text-slate-500">No deck registered yet.</span>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <div class="mt-2 grid gap-1">
                            <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Bey Picks</p>
                            <div class="grid grid-cols-3 gap-1.5">
                                @foreach ([1, 2, 3] as $beySlot)
                                    <label class="grid gap-1">
                                        <span class="text-[9px] uppercase tracking-[0.14em] text-slate-500">B{{ $beySlot }}</span>
                                        <input
                                            name="player{{ $setupCard['slot'] }}_bey{{ $beySlot }}"
                                            value="{{ $formValue('player'.$setupCard['slot'].'_bey'.$beySlot, $setupCard['storedDeck'][$beySlot - 1]) }}"
                                            placeholder="Pick"
                                            class="rounded-lg border border-slate-700/80 bg-slate-950/80 px-2 py-1.5 text-[12px] text-slate-100 placeholder:text-slate-600 focus:border-amber-500 focus:outline-none"
                                        >
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-2.5 grid gap-1.5" data-stadium-side-group data-player-slot="{{ $setupCard['slot'] }}">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Stadium Side</span>
                            <span class="text-[10px] uppercase tracking-[0.14em] text-slate-600">Auto-opposite</span>
                        </div>
                        <input type="hidden" name="player{{ $setupCard['slot'] }}_stadium_side" value="{{ $selectedSide ?? '' }}" data-stadium-side-input>
                        <div class="grid grid-cols-2 gap-1.5">
                            @foreach (['X', 'B'] as $sideCode)
                                @php
                                    $isSelected = $selectedSide === $sideCode;
                                @endphp
                                <button
                                    type="button"
                                    data-stadium-side-choice
                                    data-side-choice="{{ $sideCode }}"
                                    class="rounded-xl border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.18em] transition {{ $isSelected ? 'border-cyan-400/70 bg-cyan-400/10 text-cyan-100' : 'border-slate-700/80 bg-slate-950/70 text-slate-300 hover:border-cyan-400/45 hover:text-cyan-100' }}"
                                >
                                    {{ $sideCode }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="rounded-2xl border border-slate-800/85 bg-slate-950/70 p-3">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Battle Results</p>
                    <p class="mt-1 text-[10px] text-slate-400">First to {{ $matchWinThreshold }}. Spin = 1, Over = 2, Burst = 2, Extreme = 3.</p>
                </div>
                <div class="grid gap-1 rounded-xl border border-slate-800/85 bg-slate-900/70 px-2.5 py-1.5 text-[9px] text-slate-300 sm:grid-cols-2 sm:gap-2.5">
                    <div class="flex items-center gap-1.5">
                        <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full border border-cyan-500/35 bg-cyan-500/[0.08] px-1 text-[8px] font-semibold text-cyan-200">P1</span>
                        <span class="truncate">{{ $player1Name }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full border border-slate-700/85 bg-slate-950/80 px-1 text-[8px] font-semibold text-slate-200">P2</span>
                        <span class="truncate">{{ $player2Name }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-2.5 grid gap-2 md:grid-cols-2 xl:grid-cols-4">
                @foreach (range(1, $battleSlotCount) as $slot)
                    @include('partials.battle-result-picker', [
                        'slot' => $slot,
                        'player1Name' => $player1Name,
                        'player2Name' => $player2Name,
                        'selectedWinner' => $formValue('result_'.$slot, $match->{'result_'.$slot}),
                        'selectedType' => $formValue('result_type_'.$slot, $match->{'result_type_'.$slot}),
                    ])
                @endforeach
            </div>

            <div class="mt-3 flex justify-end">
                <button class="rounded-xl border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-amber-100 transition hover:bg-amber-500/20">{{ $submitLabel }}</button>
            </div>
        </section>
    </div>
</form>
