<x-layouts.public :title="'Match #'.$match->id" :full-bleed="true" :hide-footer="true">
    @php
        $player1Name = $match->player1->user->nickname;
        $player2Name = $match->is_bye
            ? 'BYE'
            : ($match->player2_id ? $match->player2->user->nickname : '- opponent');
        $winnerName = $match->winner_id
            ? ($match->winner_id === $match->player1_id ? $player1Name : $player2Name)
            : null;
        $roundLabel = $round?->label ?: ucfirst(str_replace('_', ' ', $match->stage)).' Round '.($match->round_number ?? 1);
    @endphp

    <div class="min-h-[calc(100svh-4.5rem)] w-full bg-slate-950 px-3 py-3 sm:px-4 sm:py-4 lg:px-5">
        <header class="mb-3 flex flex-wrap items-center justify-between gap-3 border border-slate-800/80 bg-slate-900/78 px-4 py-3">
            <div class="min-w-0">
                <p class="type-label text-[10px] text-slate-500">BBXLU MATCH VIEWER</p>
                <h1 class="type-headline mt-1 text-xl text-cyan-100 sm:text-2xl">Match #{{ $match->id }}</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('live.viewer.event', $event) }}"
                    class="type-label inline-flex items-center justify-center border border-slate-700 px-4 py-2 text-xs text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200"
                >
                    Event Board
                </a>
                <a
                    href="{{ route('home') }}"
                    class="type-label inline-flex items-center justify-center border border-slate-700 px-4 py-2 text-xs text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200"
                >
                    Home
                </a>
            </div>
        </header>

        <section class="grid gap-3 xl:grid-cols-[minmax(0,1.35fr)_minmax(18rem,0.8fr)]">
            <article class="border border-cyan-400/25 bg-slate-950/72 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="type-label text-[10px] text-cyan-300">EVENT</p>
                        <h2 class="type-headline mt-1 break-words text-2xl text-white">{{ $event->title }}</h2>
                        <p class="type-body mt-2 text-sm text-slate-300">{{ $event->date->format('D, d M Y') }} / {{ $event->eventType->name }} / {{ $roundLabel }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-300">{{ strtoupper((string) $match->status) }}</span>
                        <span class="type-label border border-cyan-400/40 px-2.5 py-1 text-[10px] text-cyan-100">FIRST TO {{ $threshold }}</span>
                    </div>
                </div>

                <div class="mt-4 border border-slate-800/80 bg-slate-900/65 p-3">
                    <div class="overflow-hidden rounded border border-slate-800/90 bg-slate-900/75">
                        <div class="grid grid-cols-[minmax(0,1fr)_4rem] items-center gap-3 px-3 py-3 text-sm {{ $match->winner_id === $match->player1_id ? 'bg-cyan-400/[0.08] font-semibold text-slate-50' : 'text-slate-200' }}">
                            <span class="truncate">{{ $player1Name }}</span>
                            <span class="text-right text-lg font-semibold {{ $match->winner_id === $match->player1_id ? 'text-cyan-100' : 'text-slate-400' }}">{{ $match->player1_score }}</span>
                        </div>
                        <div class="grid grid-cols-[minmax(0,1fr)_4rem] items-center gap-3 border-t border-slate-800/90 px-3 py-3 text-sm {{ $match->winner_id === $match->player2_id ? 'bg-cyan-400/[0.08] font-semibold text-slate-50' : 'text-slate-300' }}">
                            <span class="truncate">{{ $player2Name }}</span>
                            <span class="text-right text-lg font-semibold {{ $match->winner_id === $match->player2_id ? 'text-cyan-100' : 'text-slate-400' }}">{{ $match->player2_score }}</span>
                        </div>
                    </div>

                    <div class="mt-3 grid gap-2 sm:grid-cols-3">
                        <div class="border border-slate-800 bg-slate-950/80 px-3 py-2">
                            <p class="type-label text-[10px] text-slate-500">Match ID</p>
                            <p class="type-title mt-1 text-sm text-slate-100">#{{ $match->id }}</p>
                        </div>
                        <div class="border border-slate-800 bg-slate-950/80 px-3 py-2">
                            <p class="type-label text-[10px] text-slate-500">Stage</p>
                            <p class="type-title mt-1 text-sm text-slate-100">{{ $roundLabel }}</p>
                        </div>
                        <div class="border border-slate-800 bg-slate-950/80 px-3 py-2">
                            <p class="type-label text-[10px] text-slate-500">Winner</p>
                            <p class="type-title mt-1 text-sm text-slate-100">{{ $winnerName ?: 'Pending' }}</p>
                        </div>
                    </div>
                </div>
            </article>

            <aside class="border border-slate-800/80 bg-slate-950/72 p-4">
                <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2">
                    <h3 class="type-title text-base text-cyan-100">Battle Log</h3>
                    <span class="type-label text-[10px] text-slate-500">{{ $battleRows->count() }} entries</span>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse ($battleRows as $battle)
                        <div class="grid grid-cols-[3rem_minmax(0,1fr)_4.5rem_3rem] items-center gap-2 border border-slate-800/80 bg-slate-900/65 px-3 py-2 text-xs">
                            <span class="type-label text-slate-500">#{{ $battle['slot'] }}</span>
                            <span class="truncate text-slate-100">{{ $battle['winner_name'] }}</span>
                            <span class="text-slate-300">{{ ucfirst($battle['finish_type']) }}</span>
                            <span class="text-right font-semibold text-cyan-100">{{ $battle['points'] }} pt</span>
                        </div>
                    @empty
                        <div class="border border-dashed border-slate-700/80 bg-slate-950/55 px-3 py-5 text-center">
                            <p class="type-title text-sm text-slate-100">No battle results yet.</p>
                            <p class="type-body mt-2 text-sm text-slate-500">This match has no recorded battle log at the moment.</p>
                        </div>
                    @endforelse
                </div>
            </aside>
        </section>
    </div>
</x-layouts.public>
