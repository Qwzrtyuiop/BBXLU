<x-layouts.app :title="'Dashboard | BBXLU'">
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400">Users</p>
            <p class="mt-2 text-3xl font-bold text-amber-200">{{ $stats['users'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400">Players</p>
            <p class="mt-2 text-3xl font-bold text-amber-200">{{ $stats['players'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400">Events</p>
            <p class="mt-2 text-3xl font-bold text-amber-200">{{ $stats['events'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400">Upcoming</p>
            <p class="mt-2 text-3xl font-bold text-amber-200">{{ $stats['upcoming'] }}</p>
        </article>
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-amber-100">Upcoming Events</h2>
                <a href="{{ route('events.create') }}" class="rounded-lg border border-amber-500/60 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-amber-200 hover:bg-amber-500/10">New</a>
            </div>
            <div class="space-y-3">
                @forelse ($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event) }}" class="block rounded-lg border border-slate-800 bg-slate-950/60 p-3 hover:border-amber-500/50">
                        <p class="font-medium text-slate-100">{{ $event->title }}</p>
                        <p class="mt-1 text-sm text-slate-400">
                            {{ $event->date->format('d M Y') }} - {{ $event->eventType->name }} - by {{ $event->creator->nickname }}
                        </p>
                    </a>
                @empty
                    <p class="text-sm text-slate-400">No upcoming events yet.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="mb-4 text-lg font-semibold text-amber-100">Top Ranking</h2>
            <div class="space-y-3">
                @forelse ($leaderboard as $row)
                    <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-950/60 px-3 py-2">
                        <p class="text-sm"><span class="mr-2 text-amber-300">#{{ $row->rank }}</span>{{ $row->nickname }}</p>
                        <p class="text-sm font-semibold text-amber-200">{{ $row->points }} pts</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No ranking data yet.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="mt-8 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <h2 class="mb-4 text-lg font-semibold text-amber-100">Recent Events</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($recentEvents as $event)
                <a href="{{ route('events.show', $event) }}" class="rounded-lg border border-slate-800 bg-slate-950/60 p-3 hover:border-amber-500/50">
                    <p class="font-medium">{{ $event->title }}</p>
                    <p class="mt-1 text-xs uppercase tracking-wider text-slate-400">{{ $event->status }}</p>
                    <p class="mt-1 text-sm text-slate-400">{{ $event->date->format('d M Y') }} - {{ $event->eventType->name }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-400">No events yet.</p>
            @endforelse
        </div>
    </section>
</x-layouts.app>
