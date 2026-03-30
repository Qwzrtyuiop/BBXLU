@php
    $layout = $layout ?? 'swiss';
    $player1Name = $match->player1->user->nickname;
    $hasPlaceholderOpponent = ! $match->is_bye && ! $match->player2_id;
    $player2Name = $match->is_bye
        ? 'BYE'
        : ($match->player2_id ? $match->player2->user->nickname : '- opponent');
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
    $joinedCardClasses = 'overflow-hidden rounded border border-slate-800/90 bg-slate-900/65';
    $winnerRowClasses = 'bg-cyan-400/[0.08] font-semibold text-slate-50';
    $winnerMetricClasses = 'text-cyan-100';
    $gridColumn = $gridColumn ?? null;
    $gridRowStart = $gridRowStart ?? null;
    $bracketConnectorHeightRem = $bracketConnectorHeightRem ?? 0;
    $showBracketConnector = $showBracketConnector ?? false;
    $showBracketOutgoingConnector = $showBracketOutgoingConnector ?? false;
    $bracketOutgoingDirection = $bracketOutgoingDirection ?? 'down';
    $bracketOutgoingHalfHeightRem = $bracketOutgoingHalfHeightRem ?? 0;
    $bracketLaneWidthRem = 1.75;
    $bracketColumnGapRem = 2;
    $detailUrl = $detailUrl ?? null;
    $wrapperTag = $detailUrl ? 'a' : 'div';
    $interactiveClasses = $detailUrl ? 'transition hover:border-cyan-400/45 hover:bg-slate-900/90' : '';
@endphp

@if ($layout === 'bracket')
    <{{ $wrapperTag }}
        @if ($detailUrl) href="{{ $detailUrl }}" @endif
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
            class="relative block w-full max-w-[11.5rem] border px-2 py-1.5 {{ $cardBorderClasses }} {{ $interactiveClasses }}"
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

            <div class="{{ $joinedCardClasses }}">
                <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 px-2 py-1 text-[10px] leading-tight {{ $player1Won ? $winnerRowClasses : 'text-slate-200' }}">
                    <span class="truncate">{{ $player1Name }}</span>
                    <span class="text-right text-[9px] font-semibold {{ $player1Won ? $winnerMetricClasses : 'text-slate-400' }}">{{ $player1Metric }}</span>
                </div>
                <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 border-t border-slate-800/90 px-2 py-1 text-[10px] leading-tight {{ $player2Won ? $winnerRowClasses : 'text-slate-300' }}">
                    <span class="truncate">{{ $player2Name }}</span>
                    <span class="text-right text-[9px] font-semibold {{ $player2Won ? $winnerMetricClasses : 'text-slate-400' }}">{{ $player2Metric }}</span>
                </div>
            </div>
        </div>
    </{{ $wrapperTag }}>
@elseif ($layout === 'swiss')
    <{{ $wrapperTag }} @if ($detailUrl) href="{{ $detailUrl }}" @endif class="block px-0 py-1">
        <div class="{{ $joinedCardClasses }} {{ $interactiveClasses }}">
            <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 px-2.5 py-1.5 text-[10px] leading-tight {{ $player1Won ? $winnerRowClasses : 'text-slate-200' }}">
                <span class="truncate">{{ $player1Name }}</span>
                <span class="text-right text-[9px] font-semibold {{ $player1Won ? $winnerMetricClasses : 'text-slate-400' }}">{{ $player1Metric }}</span>
            </div>
            <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 border-t border-slate-800/90 px-2.5 py-1.5 text-[10px] leading-tight {{ $player2Won ? $winnerRowClasses : 'text-slate-300' }}">
                <span class="truncate">{{ $player2Name }}</span>
                <span class="text-right text-[9px] font-semibold {{ $player2Won ? $winnerMetricClasses : 'text-slate-400' }}">{{ $player2Metric }}</span>
            </div>
        </div>
    </{{ $wrapperTag }}>
@else
    <{{ $wrapperTag }} @if ($detailUrl) href="{{ $detailUrl }}" @endif class="block border px-2 py-1.5 {{ $cardBorderClasses }} {{ $interactiveClasses }}">
        <div class="{{ $joinedCardClasses }}">
            <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 px-2 py-1 text-[10px] leading-tight {{ $player1Won ? $winnerRowClasses : 'text-slate-200' }}">
                <span class="truncate">{{ $player1Name }}</span>
                <span class="text-right text-[9px] font-semibold {{ $player1Won ? $winnerMetricClasses : 'text-slate-400' }}">{{ $player1Metric }}</span>
            </div>
            <div class="grid grid-cols-[minmax(0,1fr)_2.15rem] items-center gap-2 border-t border-slate-800/90 px-2 py-1 text-[10px] leading-tight {{ $player2Won ? $winnerRowClasses : 'text-slate-300' }}">
                <span class="truncate">{{ $player2Name }}</span>
                <span class="text-right text-[9px] font-semibold {{ $player2Won ? $winnerMetricClasses : 'text-slate-400' }}">{{ $player2Metric }}</span>
            </div>
        </div>
    </{{ $wrapperTag }}>
@endif
