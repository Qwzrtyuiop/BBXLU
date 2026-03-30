@php
    $isNewMode = $mode === 'new';
    $selectedExistingNickname = old('selected_nicknames.0');
    $errorMessage = $errors->first('nickname')
        ?: $errors->first('selected_nicknames')
        ?: $errors->first('selected_nicknames.*')
        ?: $errors->first('deck_bey1')
        ?: $errors->first('deck_bey2')
        ?: $errors->first('deck_bey3');
@endphp

<div
    data-locked-participant-modal="{{ $modalId }}"
    data-locked-participant-open-on-load="{{ $showModal ? 'true' : 'false' }}"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/86 px-4 py-6"
>
    <div class="w-full max-w-2xl border border-cyan-400/35 bg-[linear-gradient(160deg,rgba(8,47,73,0.22)_0%,rgba(2,6,23,0.98)_100%)] p-4 shadow-[0_28px_72px_rgba(2,6,23,0.72)]">
        <div class="flex items-start justify-between gap-3 border-b border-slate-800/80 pb-3">
            <div class="min-w-0">
                <p class="type-kicker text-[10px] text-cyan-300/75">Locked Deck Registration</p>
                <h3 class="type-headline mt-1 text-lg text-cyan-100">{{ $registrationEvent->title }}</h3>
                <p class="type-body mt-1 text-[12px] text-slate-400">
                    {{ $isNewMode
                        ? 'Create a participant and save Beys 1-3 in one step.'
                        : 'Pick one registered user not yet in this event, then save Beys 1-3.' }}
                </p>
            </div>

            <button type="button" data-locked-participant-close class="type-label border border-slate-700 px-2.5 py-1 text-[9px] text-slate-100 transition hover:border-rose-400 hover:text-rose-200">
                Close
            </button>
        </div>

        <form action="{{ route('events.participants.store', $registrationEvent) }}" method="POST" class="mt-3 grid gap-3">
            @csrf
            <input type="hidden" name="dashboard_redirect" value="1">
            <input type="hidden" name="dashboard_panel" value="workspace">
            <input type="hidden" name="dashboard_event_id" value="{{ $registrationEvent->id }}">
            <input type="hidden" name="locked_participant_mode" value="{{ $mode }}">

            @if ($isNewMode)
                <label class="grid gap-1">
                    <span class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Nickname</span>
                    <input
                        name="nickname"
                        value="{{ old('nickname') }}"
                        required
                        class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none"
                    >
                </label>
            @else
                <label class="grid gap-1">
                    <span class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Existing Player</span>
                    <select
                        name="selected_nicknames[]"
                        required
                        class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none"
                    >
                        <option value="">Select a registered user</option>
                        @foreach ($registerableUsers as $user)
                            <option value="{{ $user->nickname }}" @selected($selectedExistingNickname === $user->nickname)>
                                {{ $user->nickname }}@if (! $user->is_claimed) - auto account @endif
                            </option>
                        @endforeach
                    </select>
                </label>
            @endif

            <div class="grid gap-2 sm:grid-cols-3">
                <label class="grid gap-1">
                    <span class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Bey 1</span>
                    <input
                        name="deck_bey1"
                        value="{{ old('deck_bey1') }}"
                        required
                        class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none"
                    >
                </label>
                <label class="grid gap-1">
                    <span class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Bey 2</span>
                    <input
                        name="deck_bey2"
                        value="{{ old('deck_bey2') }}"
                        required
                        class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none"
                    >
                </label>
                <label class="grid gap-1">
                    <span class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Bey 3</span>
                    <input
                        name="deck_bey3"
                        value="{{ old('deck_bey3') }}"
                        required
                        class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-[13px] text-slate-100 focus:border-amber-500 focus:outline-none"
                    >
                </label>
            </div>

            @if ($errorMessage)
                <p class="text-sm text-rose-300">{{ $errorMessage }}</p>
            @endif

            <div class="flex items-center justify-end gap-2 border-t border-slate-800/80 pt-3">
                <button type="button" data-locked-participant-close class="type-label border border-slate-700 px-3 py-1.5 text-[9px] text-slate-100 transition hover:border-slate-500 hover:text-white">
                    Cancel
                </button>
                <button type="submit" class="type-label border border-amber-400/70 bg-amber-400/12 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                    {{ $isNewMode ? 'Add New Player' : 'Add Existing Player' }}
                </button>
            </div>
        </form>
    </div>
</div>
