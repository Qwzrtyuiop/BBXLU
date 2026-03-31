@php
    $selectedWinner = $selectedWinner !== null ? (string) $selectedWinner : '';
    $selectedType = $selectedType ? (string) $selectedType : '';
    $selectedChoice = ($selectedWinner !== '' && $selectedType !== '')
        ? $selectedWinner.':'.$selectedType
        : '';
    $finishOptions = [
        ['type' => 'spin', 'label' => 'Spin', 'points' => 1],
        ['type' => 'over', 'label' => 'Over', 'points' => 2],
        ['type' => 'burst', 'label' => 'Burst', 'points' => 2],
        ['type' => 'extreme', 'label' => 'Extreme', 'points' => 3],
    ];
    $selectedClasses = 'border-amber-400/70 bg-amber-400/12 text-amber-100 shadow-[0_10px_24px_rgba(251,191,36,0.12)]';
    $idleClasses = 'border-slate-700/80 bg-slate-950/75 text-slate-300 hover:border-cyan-400/45 hover:text-cyan-100';
    $selectedSummary = collect($finishOptions)->firstWhere('type', $selectedType);
    $summaryText = ($selectedWinner !== '' && $selectedSummary)
        ? (($selectedWinner === '1' ? 'P1' : 'P2').' '.$selectedSummary['label'].' ('.$selectedSummary['points'].' pt'.($selectedSummary['points'] > 1 ? 's' : '').')')
        : 'Choose winner and finish';
@endphp

<div class="rounded-xl border border-slate-800/85 bg-slate-900/55 p-2" data-battle-picker>
    <input type="hidden" name="result_{{ $slot }}" value="{{ $selectedWinner }}" data-battle-result-winner>
    <input type="hidden" name="result_type_{{ $slot }}" value="{{ $selectedType }}" data-battle-result-type>

    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <p class="text-[9px] uppercase tracking-[0.14em] text-slate-500">Battle {{ $slot }}</p>
            <p
                class="mt-0.5 text-[9px] text-slate-400"
                data-battle-summary
                data-default-summary="Choose winner and finish"
            >
                {{ $summaryText }}
            </p>
        </div>
        <button
            type="button"
            data-battle-clear
            class="rounded-lg border border-slate-700/80 px-2 py-0.5 text-[8px] font-semibold uppercase tracking-[0.14em] text-slate-400 transition hover:border-rose-500/60 hover:text-rose-200"
        >
            Clear
        </button>
    </div>

    <div class="mt-1.5 grid w-full grid-cols-[1.85rem_repeat(4,minmax(0,1fr))] gap-1">
        @foreach ([1 => 'P1', 2 => 'P2'] as $winnerSlot => $winnerLabel)
            <div class="flex items-center justify-center rounded-lg border border-slate-800/85 bg-slate-950/75 px-1 py-1.5 text-[8px] font-semibold uppercase tracking-[0.14em] text-slate-300">
                {{ $winnerLabel }}
            </div>
            @foreach ($finishOptions as $option)
                @php
                    $choiceValue = $winnerSlot.':'.$option['type'];
                    $isSelected = $selectedChoice === $choiceValue;
                    $choiceSummary = $winnerLabel.' '.$option['label'].' ('.$option['points'].' pt'.($option['points'] > 1 ? 's' : '').')';
                @endphp
                <button
                    type="button"
                    data-battle-choice
                    data-choice="{{ $choiceValue }}"
                    data-choice-summary="{{ $choiceSummary }}"
                    class="min-h-[2.45rem] rounded-lg border px-1 py-1 text-center transition {{ $isSelected ? $selectedClasses : $idleClasses }}"
                >
                    <span class="block text-[7px] font-semibold uppercase leading-[1.05] text-inherit">{{ $option['label'] }}</span>
                    <span class="mt-0.5 block text-[9px] font-semibold leading-none text-inherit">{{ $option['points'] }}</span>
                    <span class="sr-only">{{ $choiceSummary }}</span>
                </button>
            @endforeach
        @endforeach
    </div>
</div>
