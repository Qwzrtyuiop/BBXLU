<div
    data-deck-modal
    data-deck-open-on-load="{{ $showDeckRegistrationModal ? 'true' : 'false' }}"
    data-deck-focus-player-id="{{ $oldDeckPlayerId }}"
    data-deck-bulk-action="{{ route('events.participants.decks.bulk.store', $deckRegistrationEvent) }}"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/86 px-4 py-6"
>
    <div class="w-full max-w-5xl border border-cyan-400/35 bg-[linear-gradient(160deg,rgba(8,47,73,0.22)_0%,rgba(2,6,23,0.98)_100%)] p-4 shadow-[0_28px_72px_rgba(2,6,23,0.72)]">
        <div class="flex items-start justify-between gap-3 border-b border-slate-800/80 pb-3">
            <div class="min-w-0">
                <p class="type-kicker text-[10px] text-cyan-300/75">Deck Registry</p>
                <h3 class="type-headline mt-1 text-lg text-cyan-100">{{ $deckRegistrationEvent->title }}</h3>
                <p class="type-body mt-1 text-[12px] text-slate-400">{{ $deckRegistrationDescription }}</p>
            </div>

            <button type="button" data-deck-modal-close class="type-label border border-slate-700 px-2.5 py-1 text-[9px] text-slate-100 transition hover:border-rose-400 hover:text-rose-200">
                Close
            </button>
        </div>

        @if ($errors->has('deck_bey1') || $errors->has('deck_bey2') || $errors->has('deck_bey3'))
            <div class="mt-3 border border-rose-500/45 bg-rose-500/10 px-3 py-2">
                <p class="text-[11px] text-rose-200">
                    {{ $errors->first('deck_bey1') ?: $errors->first('deck_bey2') ?: $errors->first('deck_bey3') }}
                </p>
            </div>
        @endif

        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border border-slate-800/80 bg-slate-950/50 px-3 py-2">
            <p class="text-[11px] text-slate-400">Save one row at a time or use bulk save for everything currently shown.</p>
            <button
                type="button"
                data-deck-bulk-submit
                class="type-label border border-amber-400/70 bg-amber-400/12 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20"
            >
                Bulk Save
            </button>
        </div>

        <div class="mt-3 max-h-[72vh] space-y-3 overflow-y-auto pr-1 no-scrollbar" data-deck-scroll-body>
            @foreach ($deckRegistrationTargets as $participant)
                @php
                    $useOldDeckValues = $oldDeckPlayerId === (int) $participant->player_id;
                    $bulkOldDeck = old('decks.'.$participant->player_id, []);
                @endphp
                <form
                    action="{{ route('events.participants.deck.store', [$deckRegistrationEvent, $participant->player]) }}"
                    method="POST"
                    data-deck-player-row="{{ $participant->player_id }}"
                    class="grid gap-3 border border-slate-800/80 bg-slate-950/65 p-3"
                >
                    @csrf
                    <input type="hidden" name="dashboard_redirect" value="1">
                    <input type="hidden" name="dashboard_panel" value="workspace">
                    <input type="hidden" name="dashboard_event_id" value="{{ $deckRegistrationEvent->id }}">
                    <input type="hidden" name="deck_player_id" value="{{ $participant->player_id }}">

                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-100">{{ $participant->player->user->nickname }}</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="type-label text-[9px] {{ $participant->hasRegisteredDeck() ? 'text-cyan-200' : 'text-rose-200' }}">
                                {{ $participant->hasRegisteredDeck() ? 'Ready' : 'Needs deck' }}
                            </span>
                            @if ($participant->hasRegisteredDeck())
                                <span class="type-label text-[9px] text-slate-500">{{ implode(', ', $participant->registeredBeys()) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-3 lg:grid-cols-[minmax(0,1.9fr)_auto] lg:items-end">
                        <div class="grid gap-2 sm:grid-cols-3">
                            <label class="grid gap-1">
                                <span class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Bey 1</span>
                                <input
                                    name="deck_bey1"
                                    value="{{ $bulkOldDeck['deck_bey1'] ?? ($useOldDeckValues ? old('deck_bey1', $participant->deck_bey1) : $participant->deck_bey1) }}"
                                    class="rounded-lg border border-slate-700 bg-slate-950/70 px-2.5 py-1.5 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none"
                                >
                            </label>
                            <label class="grid gap-1">
                                <span class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Bey 2</span>
                                <input
                                    name="deck_bey2"
                                    value="{{ $bulkOldDeck['deck_bey2'] ?? ($useOldDeckValues ? old('deck_bey2', $participant->deck_bey2) : $participant->deck_bey2) }}"
                                    class="rounded-lg border border-slate-700 bg-slate-950/70 px-2.5 py-1.5 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none"
                                >
                            </label>
                            <label class="grid gap-1">
                                <span class="text-[10px] uppercase tracking-[0.14em] text-slate-500">Bey 3</span>
                                <input
                                    name="deck_bey3"
                                    value="{{ $bulkOldDeck['deck_bey3'] ?? ($useOldDeckValues ? old('deck_bey3', $participant->deck_bey3) : $participant->deck_bey3) }}"
                                    class="rounded-lg border border-slate-700 bg-slate-950/70 px-2.5 py-1.5 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none"
                                >
                            </label>
                        </div>

                        <button class="type-label w-full border border-cyan-500/60 bg-cyan-500/10 px-3 py-1.5 text-[9px] text-cyan-100 transition hover:bg-cyan-500/20 lg:w-auto">
                            Save Deck
                        </button>
                    </div>
                </form>
            @endforeach
        </div>

        <form method="POST" data-deck-bulk-form class="hidden">
            @csrf
            <input type="hidden" name="dashboard_redirect" value="1">
            <input type="hidden" name="dashboard_panel" value="workspace">
            <input type="hidden" name="dashboard_event_id" value="{{ $deckRegistrationEvent->id }}">
            <div data-deck-bulk-inputs></div>
        </form>
    </div>
</div>
