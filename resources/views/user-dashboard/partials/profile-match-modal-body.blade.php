@php
    $match = $match ?? null;
    $profilePlayer = $profilePlayer ?? null;
    $playerIsPlayerOne = $profilePlayer && $match ? $match->player1_id === $profilePlayer->id : true;
    $playerName = $playerIsPlayerOne
        ? ($match?->player1?->user?->nickname ?? 'Unknown')
        : ($match?->player2?->user?->nickname ?? 'Unknown');
    $opponentName = $match?->is_bye
        ? 'BYE'
        : ($playerIsPlayerOne
            ? ($match?->player2?->user?->nickname ?? '- opponent')
            : ($match?->player1?->user?->nickname ?? 'Unknown'));
    $playerScore = $playerIsPlayerOne ? ($match?->player1_score ?? 0) : ($match?->player2_score ?? 0);
    $opponentScore = $playerIsPlayerOne ? ($match?->player2_score ?? 0) : ($match?->player1_score ?? 0);
    $playerWon = $match?->winner_id && (
        ($playerIsPlayerOne && $match->winner_id === $match->player1_id)
        || (! $playerIsPlayerOne && $match->winner_id === $match->player2_id)
    );
    $roundLabel = $match?->round?->label ?: ucfirst(str_replace('_', ' ', (string) $match?->stage)).' Round '.($match?->round_number ?? 1);
    $roundMatchCount = $match?->round
        ? ($match->round->relationLoaded('matches') ? $match->round->matches->count() : $match->round->matches()->count())
        : 1;
    $threshold = $match?->event?->battleWinThresholdForMatch($match, $match?->round, $match?->stage, $roundMatchCount) ?? 4;
    $battleRows = $match?->battleResults() ?? collect();
@endphp

<div class="space-y-4">
    <div class="border border-cyan-400/25 bg-[linear-gradient(145deg,rgba(8,47,73,0.5)_0%,rgba(2,6,23,0.94)_60%,rgba(15,23,42,0.98)_100%)] px-4 py-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="type-label border border-cyan-400/40 bg-cyan-400/10 px-2 py-1 text-[10px] text-cyan-100">{{ strtoupper((string) ($match?->status ?? 'pending')) }}</span>
                    <span class="type-label border border-slate-700 px-2 py-1 text-[10px] text-slate-300">Match #{{ $match?->id }}</span>
                </div>
                <h3 class="type-headline mt-3 break-words text-xl text-cyan-100">{{ $match?->event?->title ?? 'Unknown event' }}</h3>
                <p class="mt-1 text-sm text-slate-400">{{ $roundLabel }}</p>
                <p class="mt-1 text-sm text-slate-400">{{ $match?->event?->date?->format('D, d M Y') }}@if ($match?->event?->eventType) / {{ $match->event->eventType->name }} @endif</p>
            </div>
            <span class="type-label border border-cyan-400/40 px-2.5 py-1 text-[10px] text-cyan-100">FIRST TO {{ $threshold }}</span>
        </div>
    </div>

    <div class="border border-slate-800/80 bg-slate-950/72 p-3">
        <div class="overflow-hidden rounded border border-slate-800/90 bg-slate-900/75">
            <div class="grid grid-cols-[minmax(0,1fr)_4rem] items-center gap-3 px-3 py-3 text-sm {{ $playerWon ? 'bg-cyan-400/[0.08] font-semibold text-slate-50' : 'text-slate-200' }}">
                <span class="truncate">{{ $playerName }}</span>
                <span class="text-right text-lg font-semibold {{ $playerWon ? 'text-cyan-100' : 'text-slate-400' }}">{{ $playerScore }}</span>
            </div>
            <div class="grid grid-cols-[minmax(0,1fr)_4rem] items-center gap-3 border-t border-slate-800/90 px-3 py-3 text-sm {{ $match?->winner_id && ! $playerWon ? 'bg-cyan-400/[0.08] font-semibold text-slate-50' : 'text-slate-300' }}">
                <span class="truncate">{{ $opponentName }}</span>
                <span class="text-right text-lg font-semibold {{ $match?->winner_id && ! $playerWon ? 'text-cyan-100' : 'text-slate-400' }}">{{ $opponentScore }}</span>
            </div>
        </div>
    </div>

    <div class="grid gap-2 sm:grid-cols-3">
        <div class="border border-slate-800 bg-slate-950/80 px-3 py-2">
            <p class="type-label text-[10px] text-slate-500">Result</p>
            <p class="type-title mt-1 text-sm text-slate-100">
                @if (! $match?->winner_id)
                    Pending
                @elseif ($playerWon)
                    Win
                @else
                    Loss
                @endif
            </p>
        </div>
        <div class="border border-slate-800 bg-slate-950/80 px-3 py-2">
            <p class="type-label text-[10px] text-slate-500">Scoreline</p>
            <p class="type-title mt-1 text-sm text-slate-100">{{ $playerScore }} - {{ $opponentScore }}</p>
        </div>
        <div class="border border-slate-800 bg-slate-950/80 px-3 py-2">
            <p class="type-label text-[10px] text-slate-500">Stage</p>
            <p class="type-title mt-1 text-sm text-slate-100">{{ $roundLabel }}</p>
        </div>
    </div>

    <div class="border border-slate-800/80 bg-slate-950/72 p-3">
        <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2">
            <h4 class="type-title text-base text-cyan-100">Battle Log</h4>
            <span class="type-label text-[10px] text-slate-500">{{ $battleRows->count() }} entries</span>
        </div>

        <div class="mt-3 space-y-2">
            @forelse ($battleRows as $battle)
                @php
                    $winnerName = (int) $battle['winner'] === 1
                        ? ($match?->player1?->user?->nickname ?? 'Unknown')
                        : ($match?->player2?->user?->nickname ?? 'BYE');
                @endphp
                <div class="grid grid-cols-[3rem_minmax(0,1fr)_4.5rem_3rem] items-center gap-2 border border-slate-800/80 bg-slate-900/65 px-3 py-2 text-xs">
                    <span class="type-label text-slate-500">#{{ $battle['slot'] }}</span>
                    <span class="truncate text-slate-100">{{ $winnerName }}</span>
                    <span class="text-slate-300">{{ ucfirst((string) $battle['type']) }}</span>
                    <span class="text-right font-semibold text-cyan-100">{{ \App\Models\EventMatch::finishTypePoints($battle['type']) }} pt</span>
                </div>
            @empty
                <div class="border border-dashed border-slate-700/80 bg-slate-950/55 px-3 py-5 text-center">
                    <p class="type-title text-sm text-slate-100">No battle results yet.</p>
                    <p class="type-body mt-2 text-sm text-slate-500">This match has no recorded battle log at the moment.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
