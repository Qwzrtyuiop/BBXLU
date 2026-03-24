<x-layouts.app :title="'Events | BBXLU'">
    <section class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-amber-100">Events</h2>
            <p class="text-sm text-slate-400">Create and manage tournaments, results, awards, and matches.</p>
        </div>
        <a href="{{ route('events.create') }}" class="rounded-lg border border-amber-500/60 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-500/10">
            Create Event
        </a>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($events as $event)
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs uppercase tracking-widest text-slate-400">{{ $event->status }}</p>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('events.edit', $event) }}" class="rounded-md border border-slate-700 px-2 py-1 text-xs hover:border-amber-500 hover:text-amber-200">
                            Edit
                        </a>
                        <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Delete this event and all related records?');">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-md border border-rose-500/60 px-2 py-1 text-xs text-rose-200 hover:bg-rose-500/10">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
                <h3 class="mt-2 text-lg font-semibold text-amber-100">{{ $event->title }}</h3>
                <p class="mt-2 text-sm text-slate-400">{{ $event->date->format('d M Y') }} - {{ $event->eventType->name }}</p>
                <p class="mt-1 text-sm text-slate-400">By {{ $event->creator->nickname }}</p>
                @if ($event->location)
                    <p class="mt-1 text-sm text-slate-300">Location: {{ $event->location }}</p>
                @endif
                <a href="{{ route('events.show', $event) }}" class="mt-4 inline-block rounded-lg border border-slate-700 px-3 py-2 text-sm hover:border-amber-500 hover:text-amber-200">
                    Open Event
                </a>
            </article>
        @empty
            <p class="text-sm text-slate-400">No events yet. Create your first event.</p>
        @endforelse
    </section>
</x-layouts.app>
