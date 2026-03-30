<x-layouts.app :title="'User Dashboard | BBXLU'">
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.18fr)_22rem]">
        <section class="grid gap-6">
            <article class="rounded-2xl border border-cyan-400/30 bg-[linear-gradient(145deg,rgba(8,47,73,0.9)_0%,rgba(2,6,23,0.95)_55%,rgba(15,23,42,0.98)_100%)] p-6 shadow-[0_24px_60px_rgba(2,6,23,0.42)]">
                <p class="type-kicker text-xs text-cyan-300/80">Player Access</p>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="type-headline text-3xl text-cyan-100">{{ $user->nickname }}</h2>
                        <p class="mt-2 text-sm text-slate-300">
                            {{ $user->email ?: 'No email saved yet.' }}
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full border border-emerald-400/40 bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-200">
                        {{ $user->is_claimed ? 'Claimed Account' : 'Unclaimed Account' }}
                    </span>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-xl border border-cyan-400/20 bg-slate-950/45 px-4 py-3">
                        <p class="type-kicker text-[10px] text-cyan-300/75">Events Joined</p>
                        <p class="mt-2 text-2xl font-bold text-amber-100">{{ $stats['joined'] }}</p>
                    </article>
                    <article class="rounded-xl border border-amber-400/20 bg-slate-950/45 px-4 py-3">
                        <p class="type-kicker text-[10px] text-amber-300/75">Wins</p>
                        <p class="mt-2 text-2xl font-bold text-amber-100">{{ $stats['wins'] }}</p>
                    </article>
                    <article class="rounded-xl border border-fuchsia-400/20 bg-slate-950/45 px-4 py-3">
                        <p class="type-kicker text-[10px] text-fuchsia-300/75">Podiums</p>
                        <p class="mt-2 text-2xl font-bold text-amber-100">{{ $stats['podiums'] }}</p>
                    </article>
                    <article class="rounded-xl border border-emerald-400/20 bg-slate-950/45 px-4 py-3">
                        <p class="type-kicker text-[10px] text-emerald-300/75">Awards</p>
                        <p class="mt-2 text-2xl font-bold text-amber-100">{{ $stats['awards'] }}</p>
                    </article>
                </div>
            </article>

            <div class="grid gap-6 lg:grid-cols-2">
                <article class="rounded-2xl border border-amber-400/30 bg-[linear-gradient(160deg,rgba(251,191,36,0.09)_0%,rgba(2,6,23,0.92)_100%)] p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="type-kicker text-[10px] text-amber-300/75">Upcoming</p>
                            <h3 class="type-headline mt-1 text-xl text-amber-100">Registered Events</h3>
                        </div>
                        <a href="{{ route('home') }}" class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">
                            Public Home
                        </a>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($upcomingEvents as $event)
                            <article class="rounded-xl border border-slate-800/80 bg-slate-950/65 px-4 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-base font-semibold text-slate-100">{{ $event->title }}</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.14em] text-slate-500">
                                            {{ $event->date->format('D, d M Y') }}@if ($event->eventType) - {{ $event->eventType->name }}@endif
                                        </p>
                                    </div>
                                    <span class="rounded-lg border border-cyan-400/35 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-cyan-100">
                                        {{ $event->bracketLabel() }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-slate-400">{{ $event->location ?: 'Venue to be announced.' }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-slate-400">You are not registered for any upcoming events yet.</p>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-2xl border border-cyan-400/30 bg-[linear-gradient(160deg,rgba(8,47,73,0.18)_0%,rgba(2,6,23,0.92)_100%)] p-5">
                    <div>
                        <p class="type-kicker text-[10px] text-cyan-300/75">History</p>
                        <h3 class="type-headline mt-1 text-xl text-cyan-100">Recent Results</h3>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($recentResults as $result)
                            <article class="rounded-xl border border-slate-800/80 bg-slate-950/65 px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="truncate text-sm font-semibold text-slate-100">{{ $result->event->title }}</p>
                                    <span class="text-lg font-bold text-amber-200">#{{ $result->placement }}</span>
                                </div>
                                <p class="mt-1 text-xs uppercase tracking-[0.14em] text-slate-500">
                                    {{ $result->event->date->format('d M Y') }}@if ($result->event->eventType) - {{ $result->event->eventType->name }}@endif
                                </p>
                            </article>
                        @empty
                            <p class="text-sm text-slate-400">No recorded results yet.</p>
                        @endforelse
                    </div>
                </article>
            </div>
        </section>

        <aside class="grid content-start gap-6">
            <article class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5">
                <p class="type-kicker text-[10px] text-slate-500">Account</p>
                <div class="mt-4 space-y-3 text-sm text-slate-300">
                    <div>
                        <p class="text-xs uppercase tracking-[0.14em] text-slate-500">Nickname</p>
                        <p class="mt-1 text-base font-semibold text-slate-100">{{ $user->nickname }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.14em] text-slate-500">Name</p>
                        <p class="mt-1 text-base font-semibold text-slate-100">{{ $user->name ?: $user->nickname }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.14em] text-slate-500">Player Profile</p>
                        <p class="mt-1 text-base font-semibold text-slate-100">{{ $player ? 'Active' : 'Not linked yet' }}</p>
                    </div>
                </div>
            </article>

            <article class="rounded-2xl border border-fuchsia-300/35 bg-[linear-gradient(160deg,rgba(112,26,117,0.14)_0%,rgba(2,6,23,0.92)_100%)] p-5">
                <p class="type-kicker text-[10px] text-fuchsia-300/75">Awards</p>
                <h3 class="type-headline mt-1 text-lg text-fuchsia-100">Recent Award Calls</h3>

                <div class="mt-4 space-y-3">
                    @forelse ($recentAwards as $award)
                        <article class="rounded-xl border border-slate-800/80 bg-slate-950/65 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-100">{{ $award->award->name }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.14em] text-slate-500">
                                {{ $award->event->title }} - {{ $award->event->date->format('d M Y') }}
                            </p>
                        </article>
                    @empty
                        <p class="text-sm text-slate-400">No awards assigned yet.</p>
                    @endforelse
                </div>
            </article>
        </aside>
    </div>
</x-layouts.app>
