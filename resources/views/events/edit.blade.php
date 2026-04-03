<x-layouts.app :title="'Edit Event | BBXLU'">
    @php
        $eventStarted = $event->hasStarted();
        $canEditSwissSettings = $event->canEditSwissSettingsAfterStart();
    @endphp
    <section class="mx-auto max-w-3xl rounded-xl border border-slate-800 bg-slate-900/70 p-6">
        <h2 class="text-2xl font-bold text-amber-100">Edit Event</h2>
        <p class="mt-1 text-sm text-slate-400">
            {{ $canEditSwissSettings
                ? 'Bracket play has started. Only Swiss rounds and top cut size can still be changed before top cut is generated.'
                : ($eventStarted
                    ? 'Bracket play has started. Event details are locked for this event.'
                    : 'Update event details and bracket setup. Generated rounds stay attached unless you change them manually later.') }}
        </p>

        <form action="{{ route('events.update', $event) }}" method="POST" class="mt-6 grid gap-4">
            @csrf
            @method('PUT')

            <label class="grid gap-1">
                <span class="text-sm text-slate-300">Title</span>
                <input name="title" value="{{ old('title', $event->title) }}" required @disabled($eventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
            </label>

            <label class="grid gap-1">
                <span class="text-sm text-slate-300">Description</span>
                <textarea name="description" rows="3" @disabled($eventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">{{ old('description', $event->description) }}</textarea>
            </label>

            <div class="grid gap-4">
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Bracket Type</span>
                    <select name="bracket_type" required @disabled($eventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                        <option value="single_elim" @selected(old('bracket_type', $event->bracket_type) === 'single_elim')>Single Elimination</option>
                        <option value="swiss_single_elim" @selected(old('bracket_type', $event->bracket_type) === 'swiss_single_elim')>Swiss + Single Elimination</option>
                    </select>
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Swiss Rounds</span>
                    <input type="number" min="1" max="12" name="swiss_rounds" value="{{ old('swiss_rounds', $event->swiss_rounds ?: 5) }}" @disabled($eventStarted && ! $canEditSwissSettings) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                </label>
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Top Cut Size</span>
                    <select name="top_cut_size" @disabled($eventStarted && ! $canEditSwissSettings) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                        @foreach ([2, 4, 8, 16, 32, 64] as $size)
                            <option value="{{ $size }}" @selected((string) old('top_cut_size', $event->top_cut_size ?: 8) === (string) $size)>Top {{ $size }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Event Type</span>
                    <select name="event_type_id" required @disabled($eventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                        @foreach ($eventTypes as $type)
                            <option value="{{ $type->id }}" @selected((string) old('event_type_id', $event->event_type_id) === (string) $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Date</span>
                    <input type="date" name="date" value="{{ old('date', optional($event->date)->format('Y-m-d')) }}" required @disabled($eventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Location</span>
                    <input name="location" value="{{ old('location', $event->location) }}" @disabled($eventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                </label>

                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Status</span>
                    <select name="status" required @disabled($eventStarted) class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                        <option value="upcoming" @selected(old('status', $event->status) === 'upcoming')>Upcoming</option>
                        <option value="finished" @selected(old('status', $event->status) === 'finished')>Finished</option>
                    </select>
                </label>
            </div>

            <label class="grid gap-1">
                <span class="text-sm text-slate-300">Created By</span>
                <input value="{{ $event->creator->nickname }}" readonly class="rounded-lg border border-slate-800 bg-slate-900/80 px-3 py-2 text-slate-400 focus:outline-none">
            </label>

            <div class="mt-2 flex flex-wrap items-center gap-3">
                <button @disabled($eventStarted && ! $canEditSwissSettings) class="rounded-lg border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-100 hover:bg-amber-500/20 disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                    {{ $canEditSwissSettings ? 'Update Swiss Settings' : 'Update Event' }}
                </button>
                <a href="{{ route('events.show', $event) }}" class="text-sm text-slate-300 hover:text-slate-100">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
