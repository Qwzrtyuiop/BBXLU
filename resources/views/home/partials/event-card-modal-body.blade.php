<div class="space-y-4">
    <div class="border border-cyan-400/25 bg-[linear-gradient(145deg,rgba(8,47,73,0.5)_0%,rgba(2,6,23,0.94)_60%,rgba(15,23,42,0.98)_100%)] px-4 py-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="type-label border border-cyan-400/40 bg-cyan-400/10 px-2 py-1 text-[10px] text-cyan-100">{{ $event->eventType->name }}</span>
                    <span class="type-label border border-slate-700 px-2 py-1 text-[10px] text-slate-300">{{ strtoupper($event->status) }}</span>
                </div>
                <h3 class="type-headline mt-3 break-words text-2xl text-cyan-100">{{ $event->title }}</h3>
                <p class="mt-1 text-sm text-slate-400">{{ $event->creator->nickname }}</p>
            </div>

            <a
                href="{{ route('live.viewer.event', $event) }}"
                class="type-label inline-flex items-center justify-center border border-cyan-400/45 px-3 py-2 text-[10px] text-cyan-100 transition hover:bg-cyan-400/10"
            >
                Open Event
            </a>
        </div>
    </div>

    <div class="grid gap-2 sm:grid-cols-2">
        <article class="border border-slate-800/80 bg-slate-950/70 px-3 py-2.5">
            <p class="type-label text-[10px] text-slate-500">Date</p>
            <p class="mt-1 text-sm font-semibold text-slate-100">{{ $event->date->format('l, d M Y') }}</p>
        </article>
        <article class="border border-slate-800/80 bg-slate-950/70 px-3 py-2.5">
            <p class="type-label text-[10px] text-slate-500">Players</p>
            <p class="mt-1 text-sm font-semibold text-amber-100">{{ $event->participants_count }}</p>
        </article>
        <article class="border border-slate-800/80 bg-slate-950/70 px-3 py-2.5">
            <p class="type-label text-[10px] text-slate-500">Location</p>
            <p class="mt-1 text-sm font-semibold text-slate-100">{{ $event->location ?: 'TBD Venue' }}</p>
        </article>
        <article class="border border-slate-800/80 bg-slate-950/70 px-3 py-2.5">
            <p class="type-label text-[10px] text-slate-500">Bracket</p>
            <p class="mt-1 text-sm font-semibold text-slate-100">{{ $event->bracketLabel() }}</p>
        </article>
    </div>

    <article class="border border-slate-800/80 bg-slate-950/70 px-3 py-3">
        <p class="type-label text-[10px] text-slate-500">Description</p>
        <p class="mt-2 text-sm leading-relaxed text-slate-200">{{ $event->description ?: 'No description provided.' }}</p>
    </article>

    <div class="flex flex-wrap justify-end gap-2">
        <a
            href="{{ route('live.viewer.event', $event) }}"
            class="type-label inline-flex items-center justify-center border border-amber-400/60 bg-amber-400/10 px-3 py-2 text-[10px] text-amber-100 transition hover:bg-amber-400/18"
        >
            Expand To Event Page
        </a>
    </div>
</div>
