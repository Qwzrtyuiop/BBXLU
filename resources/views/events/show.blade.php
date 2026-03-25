<x-layouts.app :title="$event->title . ' | BBXLU'">
    <section class="mb-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-widest text-slate-400">{{ $event->status }}</p>
                <h2 class="mt-2 text-2xl font-bold text-amber-100">{{ $event->title }}</h2>
                <p class="mt-2 text-sm text-slate-300">{{ $event->date->format('d M Y') }} - {{ $event->eventType->name }} - by {{ $event->creator->nickname }}</p>
                @if ($event->location)
                    <p class="mt-1 text-sm text-slate-400">Location: {{ $event->location }}</p>
                @endif
                @if ($event->description)
                    <p class="mt-3 max-w-3xl text-sm text-slate-300">{{ $event->description }}</p>
                @endif
                @php($eventChallongeLink = $event->challonge_link ?: $event->challonge_url)
                @if ($eventChallongeLink)
                    <a
                        href="{{ $eventChallongeLink }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-3 inline-flex rounded-lg border border-emerald-500/60 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-200 hover:bg-emerald-500/10"
                    >
                        Challonge Link
                    </a>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('events.index') }}" class="rounded-lg border border-slate-700 px-3 py-2 text-sm hover:border-amber-500 hover:text-amber-200">Back to events</a>
                <a href="{{ route('events.edit', $event) }}" class="rounded-lg border border-slate-700 px-3 py-2 text-sm hover:border-amber-500 hover:text-amber-200">Edit event</a>
                <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Delete this event and all related records?');">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg border border-rose-500/60 px-3 py-2 text-sm text-rose-200 hover:bg-rose-500/10">
                        Delete event
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h3 class="text-lg font-semibold text-amber-100">Add Participant</h3>
            <p class="mt-1 text-xs text-slate-400">Auto-account rule is active: unknown nickname creates user + player automatically.</p>
            <form action="{{ route('events.participants.store', $event) }}" method="POST" class="mt-4 grid gap-3">
                @csrf
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Nickname</span>
                    <input name="nickname" value="{{ old('nickname') }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                </label>
                <button class="w-fit rounded-lg border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-100 hover:bg-amber-500/20">Add Participant</button>
            </form>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h3 class="text-lg font-semibold text-amber-100">Add / Update Result</h3>
            <form action="{{ route('events.results.store', $event) }}" method="POST" class="mt-4 grid gap-3 sm:grid-cols-2">
                @csrf
                <label class="grid gap-1 sm:col-span-2">
                    <span class="text-sm text-slate-300">Player</span>
                    <select name="player_id" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                        <option value="">Select participant</option>
                        @foreach ($participants as $participant)
                            <option value="{{ $participant->id }}" @selected((string) old('player_id') === (string) $participant->id)>
                                {{ $participant->user->nickname }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Placement (1-4)</span>
                    <input type="number" min="1" max="4" name="placement" value="{{ old('placement') }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                </label>
                <div class="flex items-end">
                    <button class="w-fit rounded-lg border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-100 hover:bg-amber-500/20">Save Result</button>
                </div>
            </form>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h3 class="text-lg font-semibold text-amber-100">Assign Award</h3>
            <form action="{{ route('events.awards.store', $event) }}" method="POST" class="mt-4 grid gap-3 sm:grid-cols-2">
                @csrf
                <label class="grid gap-1 sm:col-span-2">
                    <span class="text-sm text-slate-300">Award</span>
                    <select name="award_id" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                        @foreach ($awards as $award)
                            <option value="{{ $award->id }}" @selected((string) old('award_id') === (string) $award->id)>{{ $award->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-1 sm:col-span-2">
                    <span class="text-sm text-slate-300">Player</span>
                    <select name="player_id" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                        <option value="">Select participant</option>
                        @foreach ($participants as $participant)
                            <option value="{{ $participant->id }}" @selected((string) old('player_id') === (string) $participant->id)>
                                {{ $participant->user->nickname }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <div class="sm:col-span-2">
                    <button class="w-fit rounded-lg border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-100 hover:bg-amber-500/20">Save Award</button>
                </div>
            </form>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h3 class="text-lg font-semibold text-amber-100">Record Match</h3>
            <p class="mt-1 text-xs text-slate-400">Winner is automatically set from higher score. Ties are blocked.</p>
            <form action="{{ route('events.matches.store', $event) }}" method="POST" class="mt-4 grid gap-3 sm:grid-cols-2">
                @csrf
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Player 1</span>
                    <select name="player1_id" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                        <option value="">Select participant</option>
                        @foreach ($participants as $participant)
                            <option value="{{ $participant->id }}" @selected((string) old('player1_id') === (string) $participant->id)>
                                {{ $participant->user->nickname }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Player 2</span>
                    <select name="player2_id" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                        <option value="">Select participant</option>
                        @foreach ($participants as $participant)
                            <option value="{{ $participant->id }}" @selected((string) old('player2_id') === (string) $participant->id)>
                                {{ $participant->user->nickname }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Player 1 Score</span>
                    <input type="number" min="0" name="player1_score" value="{{ old('player1_score') }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                </label>
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Player 2 Score</span>
                    <input type="number" min="0" name="player2_score" value="{{ old('player2_score') }}" required class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                </label>
                <label class="grid gap-1 sm:col-span-2">
                    <span class="text-sm text-slate-300">Round Number (optional)</span>
                    <input type="number" min="1" name="round_number" value="{{ old('round_number') }}" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none">
                </label>
                <div class="sm:col-span-2">
                    <button class="w-fit rounded-lg border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-100 hover:bg-amber-500/20">Record Match</button>
                </div>
            </form>
        </article>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-2">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h3 class="mb-3 text-lg font-semibold text-amber-100">Participants</h3>
            <div class="space-y-2">
                @forelse ($participants as $participant)
                    <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-800 bg-slate-950/60 px-3 py-2 text-sm">
                        <div>
                            {{ $participant->user->nickname }}
                            @if (! $participant->user->is_claimed)
                                <span class="ml-2 rounded border border-amber-500/60 px-2 py-0.5 text-xs text-amber-300">Auto</span>
                            @endif
                        </div>
                        <form action="{{ route('events.participants.destroy', [$event, $participant]) }}" method="POST" onsubmit="return confirm('Remove this participant from the event?');">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-md border border-rose-500/60 px-2 py-1 text-xs text-rose-200 hover:bg-rose-500/10">Remove</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No participants yet.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h3 class="mb-3 text-lg font-semibold text-amber-100">Results</h3>
            <div class="space-y-2">
                @forelse ($results as $result)
                    <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-800 bg-slate-950/60 px-3 py-2 text-sm">
                        <span>{{ $result->player->user->nickname }} - #{{ $result->placement }}</span>
                        <form action="{{ route('events.results.destroy', [$event, $result]) }}" method="POST" onsubmit="return confirm('Delete this result?');">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-md border border-rose-500/60 px-2 py-1 text-xs text-rose-200 hover:bg-rose-500/10">Delete</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No results yet.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h3 class="mb-3 text-lg font-semibold text-amber-100">Awards</h3>
            <div class="space-y-2">
                @forelse ($eventAwards as $eventAward)
                    <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-800 bg-slate-950/60 px-3 py-2 text-sm">
                        <span>{{ $eventAward->award->name }} - {{ $eventAward->player->user->nickname }}</span>
                        <form action="{{ route('events.awards.destroy', [$event, $eventAward]) }}" method="POST" onsubmit="return confirm('Delete this award assignment?');">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-md border border-rose-500/60 px-2 py-1 text-xs text-rose-200 hover:bg-rose-500/10">Delete</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No awards assigned yet.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h3 class="mb-3 text-lg font-semibold text-amber-100">Matches</h3>
            <div class="space-y-2">
                @forelse ($matches as $match)
                    <div class="flex items-start justify-between gap-2 rounded-lg border border-slate-800 bg-slate-950/60 px-3 py-2 text-sm">
                        <div>
                            <p class="font-medium">
                                {{ $match->player1->user->nickname }} {{ $match->player1_score }} - {{ $match->player2_score }} {{ $match->player2->user->nickname }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                Winner: {{ $match->winner->user->nickname }}
                                @if ($match->round_number)
                                    - Round {{ $match->round_number }}
                                @endif
                            </p>
                        </div>
                        <form action="{{ route('events.matches.destroy', [$event, $match]) }}" method="POST" onsubmit="return confirm('Delete this match?');">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-md border border-rose-500/60 px-2 py-1 text-xs text-rose-200 hover:bg-rose-500/10">Delete</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No matches recorded yet.</p>
                @endforelse
            </div>
        </article>
    </section>
</x-layouts.app>
