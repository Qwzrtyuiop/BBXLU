<x-layouts.app :title="'Create Event | BBXLU'">
    <section class="mx-auto max-w-3xl rounded-xl border border-slate-800 bg-slate-900/70 p-6">
        <h2 class="text-2xl font-bold text-amber-100">Create Event</h2>
        <p class="mt-1 text-sm text-slate-400">Set the event details and choose whether this will run as single elimination only or Swiss into top cut.</p>

        <form action="{{ route('events.store') }}" method="POST" class="mt-6 grid gap-4">
            @csrf

            <label class="grid gap-1">
                <span class="text-sm text-slate-300">Title</span>
                <input name="title" value="{{ old('title') }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
            </label>

            <label class="grid gap-1">
                <span class="text-sm text-slate-300">Description</span>
                <textarea name="description" rows="3" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">{{ old('description') }}</textarea>
            </label>

            <div class="grid gap-4">
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Bracket Type</span>
                    <select name="bracket_type" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                        <option value="single_elim" @selected(old('bracket_type', 'single_elim') === 'single_elim')>Single Elimination</option>
                        <option value="swiss_single_elim" @selected(old('bracket_type') === 'swiss_single_elim')>Swiss + Single Elimination</option>
                    </select>
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Swiss Rounds</span>
                    <input type="number" min="1" max="12" name="swiss_rounds" value="{{ old('swiss_rounds', 5) }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                </label>
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Top Cut Size</span>
                    <input type="number" min="2" max="64" name="top_cut_size" value="{{ old('top_cut_size', 8) }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                </label>
            </div>

            <p class="text-sm text-slate-500">Top cut accepts any value from 2 to 64. The bracket will pad to the next elimination size with byes when needed.</p>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Event Type</span>
                    <select name="event_type_id" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                        @foreach ($eventTypes as $type)
                            <option value="{{ $type->id }}" @selected((string) old('event_type_id') === (string) $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Date</span>
                    <input type="date" name="date" value="{{ old('date') }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Location</span>
                    <input name="location" value="{{ old('location') }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                </label>

                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Status</span>
                    <select name="status" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                        <option value="upcoming" @selected(old('status', 'upcoming') === 'upcoming')>Upcoming</option>
                        <option value="finished" @selected(old('status') === 'finished')>Finished</option>
                    </select>
                </label>
            </div>

            <label class="grid gap-1">
                <span class="text-sm text-slate-300">Created By</span>
                <input value="{{ auth()->user()->nickname }}" readonly class="rounded-lg border border-slate-800 bg-slate-900/80 px-3 py-2 text-slate-400 focus:outline-none">
            </label>

            <div class="mt-2 flex flex-wrap items-center gap-3">
                <button class="rounded-lg border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-100 hover:bg-amber-500/20">
                    Save Event
                </button>
                <a href="{{ route('events.index') }}" class="text-sm text-slate-300 hover:text-slate-100">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
