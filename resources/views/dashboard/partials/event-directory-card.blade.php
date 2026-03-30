<article class="border {{ $selectedEvent && $selectedEvent->id === $event->id ? 'border-amber-400/60' : ($event->is_active ? 'border-emerald-400/50' : 'border-slate-800/80') }} bg-slate-950/65 p-3">
    @php
        $eventStarted = $event->hasStarted();
    @endphp
    <div class="flex items-center justify-between gap-2">
        <p class="type-label text-[10px] text-slate-500">{{ $event->status }}</p>
        <div class="flex flex-wrap items-center justify-end gap-1">
            @if ($event->is_lock_deck)
                <span class="type-label border border-amber-400/60 bg-amber-500/10 px-2 py-0.5 text-[9px] text-amber-100">Lock Deck</span>
            @endif
            @if ($eventStarted)
                <span class="type-label border border-rose-500/45 bg-rose-500/10 px-2 py-0.5 text-[9px] text-rose-200">Started</span>
            @endif
            @if ($event->is_active)
                <span class="type-label border border-emerald-400/60 bg-emerald-500/10 px-2 py-0.5 text-[9px] text-emerald-100">Active</span>
            @endif
        </div>
    </div>
    <p class="type-title mt-1 break-words text-sm text-slate-100">{{ $event->title }}</p>
    <p class="type-label mt-1 text-[9px] text-slate-500">{{ $event->date->format('d M Y') }} - {{ $event->eventType->name }} - {{ $event->participants_count }} players</p>
    <p class="type-body mt-2 text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($event->description ?: ($event->location ?: 'No description.'), 88) }}</p>

    <div class="mt-3 flex flex-wrap gap-2">
        <button
            type="button"
            data-event-preview-open
            data-event-preview-template-id="event-preview-template-{{ $event->id }}"
            class="type-label border border-amber-400/70 bg-amber-400/12 px-2.5 py-1 text-[10px] text-amber-100 transition hover:bg-amber-400/20"
        >
            View
        </button>
        <a href="{{ route('dashboard', ['panel' => 'events', 'event' => $event->id]) }}" class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200">{{ $eventStarted ? 'View Details' : 'Edit Here' }}</a>
        @if ($event->is_active)
            <a href="{{ route('dashboard', ['panel' => 'workspace']) }}" class="type-label border border-slate-700 px-2.5 py-1 text-[10px] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">Workspace</a>
        @elseif (in_array($event->status, ['upcoming', 'finished'], true))
            <form action="{{ route('events.activate', $event) }}" method="POST">
                @csrf
                <input type="hidden" name="dashboard_redirect" value="1">
                <input type="hidden" name="dashboard_panel" value="events">
                <input type="hidden" name="dashboard_event_id" value="{{ $event->id }}">
                <button class="type-label border border-emerald-500/60 px-2.5 py-1 text-[10px] text-emerald-100 transition hover:bg-emerald-500/10">Set Active</button>
            </form>
        @endif
        <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Delete this event and all related records?');">
            @csrf
            @method('DELETE')
            <input type="hidden" name="dashboard_redirect" value="1">
            <input type="hidden" name="dashboard_panel" value="events">
            <button class="type-label border border-rose-500/60 px-2.5 py-1 text-[10px] text-rose-200 transition hover:bg-rose-500/10">Delete</button>
        </form>
    </div>
</article>
