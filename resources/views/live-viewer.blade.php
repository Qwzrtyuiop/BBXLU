<x-layouts.public :title="$ongoingTournament ? $ongoingTournament->title.' / Live Viewer' : 'Live Viewer'" :full-bleed="true" :hide-footer="true">
    <div class="min-h-[calc(100svh-4.5rem)] w-full bg-slate-950 px-3 py-3 sm:px-4 sm:py-4 lg:px-5">
        <header class="mb-3 flex flex-wrap items-center justify-between gap-3 border border-slate-800/80 bg-slate-900/78 px-4 py-3">
            <div class="min-w-0">
                <p class="type-label text-[10px] text-slate-500">BBXLU</p>
                <h1 class="type-headline mt-1 text-xl text-cyan-100 sm:text-2xl">
                    {{ $ongoingTournament ? $ongoingTournament->title : 'Live Viewer' }}
                </h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($ongoingTournament)
                    <span class="type-label border border-slate-700 px-3 py-2 text-[10px] text-slate-400">
                        Event #{{ $ongoingTournament->id }}
                    </span>
                @endif
                <a
                    href="{{ route('home') }}"
                    class="type-label inline-flex items-center justify-center border border-slate-700 px-4 py-2 text-xs text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200"
                >
                    Home
                </a>
            </div>
        </header>

        @if ($ongoingTournament)
            @include('home.partials.live-event-board')
        @else
            <section class="border border-slate-800/80 bg-slate-900/72 px-6 py-10 text-center">
                <h2 class="type-title mt-2 text-xl text-slate-100">No active event right now</h2>
                <p class="type-body mx-auto mt-3 max-w-xl text-sm text-slate-400">Set an event active in the dashboard to show the board here.</p>
            </section>
        @endif
    </div>
</x-layouts.public>
