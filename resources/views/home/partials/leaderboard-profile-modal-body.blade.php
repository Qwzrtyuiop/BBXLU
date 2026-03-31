@php
    $preview = $preview ?? [];
@endphp

<div class="space-y-4">
    <div class="border border-cyan-400/25 bg-[linear-gradient(145deg,rgba(8,47,73,0.5)_0%,rgba(2,6,23,0.94)_60%,rgba(15,23,42,0.98)_100%)] px-4 py-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="type-label border border-cyan-400/40 bg-cyan-400/10 px-2 py-1 text-[10px] text-cyan-100">
                        {{ ! empty($preview['is_ranked']) && ! empty($preview['rank']) ? 'RANK #'.$preview['rank'] : 'UNRANKED' }}
                    </span>
                    <span class="type-label border border-slate-700 px-2 py-1 text-[10px] text-slate-300">{{ ! empty($preview['is_claimed']) ? 'Claimed' : 'Unclaimed' }}</span>
                </div>
                <h3 class="type-headline mt-3 break-words text-2xl text-cyan-100">{{ $preview['nickname'] ?? 'Unknown player' }}</h3>
                <p class="mt-1 text-sm text-slate-400">{{ $preview['name'] ?? ($preview['nickname'] ?? 'Unknown player') }}</p>
            </div>
            <a
                href="{{ route('user.dashboard.profile', $preview['player_id']) }}"
                class="type-label inline-flex items-center justify-center border border-cyan-400/45 px-3 py-2 text-[10px] text-cyan-100 transition hover:bg-cyan-400/10"
            >
                Open Full Profile
            </a>
        </div>
    </div>

    <div class="grid gap-2 sm:grid-cols-2">
        @foreach ([
            ['label' => 'Points', 'value' => $preview['points'] ?? 0, 'tone' => 'text-cyan-100'],
            ['label' => 'Events', 'value' => $preview['events_played'] ?? 0, 'tone' => 'text-amber-100'],
            ['label' => 'Titles', 'value' => $preview['first_places'] ?? 0, 'tone' => 'text-emerald-100'],
            ['label' => 'Podiums', 'value' => $preview['podiums'] ?? 0, 'tone' => 'text-fuchsia-100'],
            ['label' => 'Awards', 'value' => $preview['awards'] ?? 0, 'tone' => 'text-fuchsia-100'],
            ['label' => 'Record', 'value' => ($preview['match_wins'] ?? 0).'-'.($preview['match_losses'] ?? 0), 'tone' => 'text-slate-100'],
            ['label' => 'Win Rate', 'value' => isset($preview['win_rate']) ? number_format((float) $preview['win_rate'], 1).'%' : '-', 'tone' => 'text-cyan-100'],
            ['label' => 'Avg Score', 'value' => isset($preview['avg_score']) ? number_format((float) $preview['avg_score'], 1) : '-', 'tone' => 'text-slate-100'],
        ] as $stat)
            <article class="border border-slate-800/80 bg-slate-950/70 px-3 py-2.5">
                <p class="type-label text-[10px] text-slate-500">{{ $stat['label'] }}</p>
                <p class="mt-1 text-lg font-bold {{ $stat['tone'] }}">{{ $stat['value'] }}</p>
            </article>
        @endforeach
    </div>

    <article class="border border-slate-800/80 bg-slate-950/70 px-3 py-3">
        <p class="type-label text-[10px] text-slate-500">Most Used Bey</p>
        <div class="mt-2 flex items-center justify-between gap-3">
            <p class="text-sm font-semibold text-slate-100">{{ $preview['most_used_bey'] ?: 'No registered Bey data yet' }}</p>
            @if (! empty($preview['most_used_bey']))
                <span class="type-label border border-amber-500/35 bg-amber-500/10 px-2 py-1 text-[10px] text-amber-200">{{ $preview['most_used_bey_count'] }} uses</span>
            @endif
        </div>
    </article>
</div>
