<x-layouts.app :title="'Players | BBXLU'">
    <section class="grid gap-6 xl:grid-cols-3">
        <article class="xl:col-span-2 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="mb-4 text-2xl font-bold text-amber-100">Leaderboard</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400">
                            <th class="px-3 py-2">Rank</th>
                            <th class="px-3 py-2">Nickname</th>
                            <th class="px-3 py-2">Points</th>
                            <th class="px-3 py-2">Events</th>
                            <th class="px-3 py-2">Firsts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaderboard as $row)
                            <tr class="border-b border-slate-900">
                                <td class="px-3 py-2 text-amber-200">#{{ $row->rank }}</td>
                                <td class="px-3 py-2">{{ $row->nickname }}</td>
                                <td class="px-3 py-2 font-semibold text-amber-100">{{ $row->points }}</td>
                                <td class="px-3 py-2 text-slate-300">{{ $row->events_played }}</td>
                                <td class="px-3 py-2 text-slate-300">{{ $row->first_places }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-3 text-slate-400">No ranking data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h3 class="mb-3 text-lg font-semibold text-amber-100">Players (All)</h3>
            <div class="space-y-2">
                @forelse ($players as $player)
                    <div class="rounded-lg border border-slate-800 bg-slate-950/60 px-3 py-2 text-sm">
                        <p>{{ $player->user->nickname }}</p>
                        <p class="text-xs text-slate-400">
                            player_id: {{ $player->id }}
                            @if (! $player->user->is_claimed)
                                - auto account
                            @endif
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No players yet.</p>
                @endforelse
            </div>
        </article>
    </section>

    @if ($playersWithoutResults->isNotEmpty())
        <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h3 class="mb-3 text-lg font-semibold text-amber-100">No Results Yet</h3>
            <div class="flex flex-wrap gap-2">
                @foreach ($playersWithoutResults as $player)
                    <span class="rounded-full border border-slate-700 bg-slate-950/60 px-3 py-1 text-xs">{{ $player->user->nickname }}</span>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
