<div
    data-participants-modal
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/86 px-4 py-6"
>
    @php
        $participantsLocked = $participantEvent->hasStarted();
    @endphp
    <div class="w-full max-w-4xl border border-amber-400/35 bg-[linear-gradient(160deg,rgba(251,191,36,0.08)_0%,rgba(2,6,23,0.98)_100%)] p-4 shadow-[0_28px_72px_rgba(2,6,23,0.72)]">
        <div class="flex items-start justify-between gap-3 border-b border-slate-800/80 pb-3">
            <div class="min-w-0">
                <p class="type-kicker text-[10px] text-amber-300/75">Participants</p>
                <h3 class="type-headline mt-1 text-lg text-amber-100">{{ $participantEvent->title }}</h3>
                <p class="type-body mt-1 text-[12px] text-slate-400">Full event roster with deck readiness and participant controls.</p>
                @if ($participantsLocked)
                    <p class="mt-1 text-[11px] text-rose-300">Roster changes are locked because bracket play has started.</p>
                @endif
            </div>

            <button type="button" data-participants-modal-close class="type-label border border-slate-700 px-2.5 py-1 text-[9px] text-slate-100 transition hover:border-rose-400 hover:text-rose-200">
                Close
            </button>
        </div>

        <div class="mt-3 max-h-[72vh] space-y-2 overflow-y-auto pr-1 no-scrollbar">
            @forelse ($participantEventParticipants as $participant)
                <div class="flex items-center justify-between gap-3 border border-slate-800/80 bg-slate-950/65 px-3 py-2">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-100">{{ $participant->player->user->nickname }}</p>
                        <div class="mt-1 flex flex-wrap gap-1">
                            @if ($participant->hasRegisteredDeck())
                                <span class="type-label border border-cyan-500/50 bg-cyan-500/10 px-2 py-0.5 text-[9px] text-cyan-100">Deck ready</span>
                            @elseif ($participant->requiresDeckFor($participantEvent, $participantDeckGateActive))
                                <span class="type-label border border-rose-500/50 bg-rose-500/10 px-2 py-0.5 text-[9px] text-rose-200">Deck needed</span>
                            @endif
                            @if (! $participant->player->user->is_claimed)
                                <span class="type-label border border-amber-500/45 bg-amber-500/10 px-2 py-0.5 text-[9px] text-amber-200">auto account</span>
                            @endif
                        </div>
                        @if ($participant->hasRegisteredDeck())
                            <p class="type-body mt-1 text-[11px] text-slate-400">{{ implode(', ', $participant->registeredBeys()) }}</p>
                        @endif
                    </div>

                    @if ($participantsLocked)
                        <span class="type-label border border-slate-700 px-2 py-1 text-[9px] text-slate-500">Locked</span>
                    @else
                        <form action="{{ route('events.participants.destroy', [$participantEvent, $participant->player]) }}" method="POST" onsubmit="return confirm('Remove this participant from the event?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="dashboard_redirect" value="1">
                            <input type="hidden" name="dashboard_panel" value="workspace">
                            <input type="hidden" name="dashboard_event_id" value="{{ $participantEvent->id }}">
                            <button class="type-label border border-rose-500/60 px-2 py-1 text-[9px] text-rose-200 transition hover:bg-rose-500/10">Remove</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="type-body text-sm text-slate-400">No participants yet.</p>
            @endforelse
        </div>
    </div>
</div>
