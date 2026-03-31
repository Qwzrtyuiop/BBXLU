@php
    $selectedWinner = $selectedWinner !== null ? (string) $selectedWinner : '';
    $selectedType = $selectedType ? (string) $selectedType : '';
    $selectedChoice = ($selectedWinner !== '' && $selectedType !== '')
        ? $selectedWinner.':'.$selectedType
        : '';
    $finishOptions = [
        ['type' => 'spin', 'label' => 'S', 'points' => 1],
        ['type' => 'over', 'label' => 'O', 'points' => 2],
        ['type' => 'burst', 'label' => 'B', 'points' => 2],
        ['type' => 'extreme', 'label' => 'E', 'points' => 3],
    ];
    $selectedClasses = 'border-amber-400/70 bg-amber-400/12 text-amber-100 shadow-[0_10px_24px_rgba(251,191,36,0.12)]';
    $idleClasses = 'border-slate-700/80 bg-slate-950/75 text-slate-300 hover:border-cyan-400/45 hover:text-cyan-100';
@endphp

<div class="border border-slate-800/80 bg-slate-900/55 p-2" data-battle-picker>
    <input type="hidden" name="result_{{ $slot }}" value="{{ $selectedWinner }}" data-battle-result-winner>
    <input type="hidden" name="result_type_{{ $slot }}" value="{{ $selectedType }}" data-battle-result-type>

    <div class="flex items-center justify-between gap-2">
        <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Battle {{ $slot }}</p>
        <button
            type="button"
            data-battle-clear
            class="border border-slate-700/80 px-2 py-1 text-[9px] font-semibold uppercase tracking-[0.14em] text-slate-400 transition hover:border-rose-500/60 hover:text-rose-200"
        >
            Clear
        </button>
    </div>

    <p class="mt-2 text-[10px] text-slate-500">
        <span class="font-semibold text-slate-300">P1</span> {{ $player1Name }}
        <span class="mx-2 text-slate-700">/</span>
        <span class="font-semibold text-slate-300">P2</span> {{ $player2Name }}
    </p>

    <div class="mt-2 overflow-x-auto">
        <div class="grid min-w-[15rem] grid-cols-[2.35rem_repeat(4,minmax(0,1fr))] gap-1">
            @foreach ([1 => 'P1', 2 => 'P2'] as $winnerSlot => $winnerLabel)
                <div class="flex items-center justify-center border border-slate-700/80 bg-slate-950/70 px-1 py-1.5 text-[10px] font-semibold text-slate-100">
                    {{ $winnerLabel }}
                </div>
                @foreach ($finishOptions as $option)
                    @php
                        $choiceValue = $winnerSlot.':'.$option['type'];
                        $isSelected = $selectedChoice === $choiceValue;
                    @endphp
                    <button
                        type="button"
                        data-battle-choice
                        data-choice="{{ $choiceValue }}"
                        class="min-h-[2.2rem] border px-1 text-center transition {{ $isSelected ? $selectedClasses : $idleClasses }}"
                    >
                        <span class="block text-[9px] font-semibold uppercase leading-none">{{ $option['type'] }}</span>
                        <span class="mt-0.5 block text-[10px] font-semibold leading-none">{{ $option['points'] }}</span>
                        <span class="sr-only">{{ $winnerLabel }} {{ ucfirst($option['type']) }}</span>
                    </button>
                @endforeach
            @endforeach
        </div>
    </div>
</div>
