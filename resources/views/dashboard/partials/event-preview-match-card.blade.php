@php
    $layout = $layout ?? 'swiss';
    $player1Name = $match->player1->user->nickname;
    $player2Name = $match->player2?->user->nickname ?? 'BYE';
    $isCompleted = $match->status === 'completed';
    $player1Won = $match->winner_id === $match->player1_id;
    $player2Won = $match->winner_id === $match->player2_id;
    $player1Metric = $match->is_bye
        ? ($player1Won ? 'BYE' : '-')
        : ($isCompleted ? (string) $match->player1_score : '-');
    $player2Metric = $match->is_bye
        ? ($player2Won ? 'BYE' : '-')
        : ($isCompleted ? (string) $match->player2_score : '-');
    $cardBorderClasses = $match->is_bye
        ? 'border-emerald-500/35 bg-emerald-500/[0.05]'
        : ($isCompleted ? 'border-cyan-500/35 bg-cyan-500/[0.04]' : 'border-slate-800/90 bg-slate-950/80');
    $gridColumn = $gridColumn ?? null;
    $gridRowStart = $gridRowStart ?? null;
    $bracketConnectorHeightRem = $bracketConnectorHeightRem ?? 0;
    $showBracketConnector = $showBracketConnector ?? false;
    $showBracketOutgoingConnector = $showBracketOutgoingConnector ?? false;
    $bracketOutgoingDirection = $bracketOutgoingDirection ?? 'down';
    $bracketOutgoingHalfHeightRem = $bracketOutgoingHalfHeightRem ?? 0;
    $bracketLaneWidthRem = 1.75;
    $bracketColumnGapRem = 2;
@endphp

@if ($layout === 'bracket')
    <div
        class="relative flex h-full min-h-[4.75rem] items-center overflow-visible"
        @if ($gridColumn !== null && $gridRowStart !== null)
            style="grid-column: {{ $gridColumn }}; grid-row: {{ $gridRowStart }};"
        @endif
    >
        @if ($showBracketConnector)
            <span
                class="absolute top-1/2 h-px -translate-y-1/2 bg-white"
                style="left: 0; width: {{ $bracketLaneWidthRem }}rem;"
            ></span>
            <span
                class="absolute w-px bg-white"
                style="left: 0; top: calc(50% - {{ $bracketConnectorHeightRem / 2 }}rem); height: {{ $bracketConnectorHeightRem }}rem;"
            ></span>
        @endif

        <div
            class="relative block w-full max-w-[11.5rem] border px-2 py-1.5 {{ $cardBorderClasses }}"
            style="margin-left: {{ $bracketLaneWidthRem }}rem;"
        >
            @if ($showBracketOutgoingConnector)
                <span
                    class="absolute top-1/2 h-px -translate-y-1/2 bg-white"
                    style="left: calc(100% + 1px); width: {{ $bracketColumnGapRem }}rem;"
                ></span>
                <span
                    class="absolute w-px bg-white"
                    style="
                        left: calc(100% + {{ $bracketColumnGapRem }}rem + 1px);
                        top: {{ $bracketOutgoingDirection === 'down' ? '50%' : 'calc(50% - '.$bracketOutgoingHalfHeightRem.'rem)' }};
                        height: {{ $bracketOutgoingHalfHeightRem }}rem;
                    "
                ></span>
            @endif

            <div class="grid gap-1">
                <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 rounded border px-2 py-1 text-[10px] leading-tight {{ $player1Won ? 'border-amber-400/40 bg-amber-500/14 font-semibold text-slate-50' : 'border-slate-800/90 bg-slate-900/70 text-slate-200' }}">
                    <span class="truncate">{{ $player1Name }}</span>
                    <span class="text-right text-[9px] font-semibold {{ $player1Won ? 'text-amber-100' : 'text-slate-400' }}">{{ $player1Metric }}</span>
                </div>
                <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 rounded border px-2 py-1 text-[10px] leading-tight {{ $player2Won ? 'border-amber-400/40 bg-amber-500/14 font-semibold text-slate-50' : 'border-slate-800/90 bg-slate-900/70 text-slate-300' }}">
                    <span class="truncate">{{ $player2Name }}</span>
                    <span class="text-right text-[9px] font-semibold {{ $player2Won ? 'text-amber-100' : 'text-slate-400' }}">{{ $player2Metric }}</span>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="border px-2 py-1.5 {{ $cardBorderClasses }}">
        <div class="grid gap-1">
            <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 rounded border px-2 py-1 text-[10px] leading-tight {{ $player1Won ? 'border-amber-400/40 bg-amber-500/14 font-semibold text-slate-50' : 'border-slate-800/90 bg-slate-900/70 text-slate-200' }}">
                <span class="truncate">{{ $player1Name }}</span>
                <span class="text-right text-[9px] font-semibold {{ $player1Won ? 'text-amber-100' : 'text-slate-400' }}">{{ $player1Metric }}</span>
            </div>
            <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 rounded border px-2 py-1 text-[10px] leading-tight {{ $player2Won ? 'border-amber-400/40 bg-amber-500/14 font-semibold text-slate-50' : 'border-slate-800/90 bg-slate-900/70 text-slate-300' }}">
                <span class="truncate">{{ $player2Name }}</span>
                <span class="text-right text-[9px] font-semibold {{ $player2Won ? 'text-amber-100' : 'text-slate-400' }}">{{ $player2Metric }}</span>
            </div>
        </div>
    </div>
@endif
