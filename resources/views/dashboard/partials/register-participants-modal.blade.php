<div
    data-register-modal
    data-register-open-on-load="{{ $showRegistrationModal ? 'true' : 'false' }}"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/86 px-4 py-6"
>
    <div class="w-full max-w-3xl border border-cyan-400/35 bg-[linear-gradient(160deg,rgba(8,47,73,0.22)_0%,rgba(2,6,23,0.98)_100%)] p-4 shadow-[0_28px_72px_rgba(2,6,23,0.72)]">
        <div class="flex items-start justify-between gap-3 border-b border-slate-800/80 pb-3">
            <div class="min-w-0">
                <p class="type-kicker text-[10px] text-cyan-300/75">Register Players</p>
                <h3 class="type-headline mt-1 text-lg text-cyan-100">{{ $registrationEvent->title }}</h3>
                <p class="type-body mt-1 text-[12px] text-slate-400">Select registered users or add a new nickname, then confirm the list below.</p>
            </div>

            <button type="button" data-register-modal-close class="type-label border border-slate-700 px-2.5 py-1 text-[9px] text-slate-100 transition hover:border-rose-400 hover:text-rose-200">
                Close
            </button>
        </div>

        <form action="{{ route('events.participants.store', $registrationEvent) }}" method="POST" data-register-form class="mt-3 grid gap-3">
            @csrf
            <input type="hidden" name="dashboard_redirect" value="1">
            <input type="hidden" name="dashboard_panel" value="{{ $registrationPanel }}">
            <input type="hidden" name="dashboard_event_id" value="{{ $registrationEvent->id }}">

            <div class="grid gap-3 lg:grid-cols-[minmax(0,1.08fr)_minmax(0,.92fr)]">
                <div class="grid gap-3">
                    <section class="border border-slate-800/80 bg-slate-950/55 p-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="type-title text-[13px] text-amber-100">Registered Users</p>
                            <span class="type-label text-[8px] text-slate-500">{{ $registerableUsers->count() }} available</span>
                        </div>

                        <label class="mt-2 grid gap-1">
                            <span class="text-xs text-slate-400">Select players</span>
                            <select data-register-existing multiple size="7" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                                @forelse ($registerableUsers as $user)
                                    <option value="{{ $user->nickname }}">
                                        {{ $user->nickname }}@if (! $user->is_claimed) - auto account @endif
                                    </option>
                                @empty
                                    <option value="" disabled>No registered users available</option>
                                @endforelse
                            </select>
                        </label>

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <button type="button" data-register-existing-add class="type-label border border-amber-400/70 bg-amber-400/12 px-2.5 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20">
                                Add Selected
                            </button>
                            <p data-register-feedback class="text-[11px] text-slate-500">Choose one or more registered users.</p>
                        </div>
                    </section>

                    <section class="border border-slate-800/80 bg-slate-950/55 p-3">
                        <p class="type-title text-[13px] text-cyan-100">Add New User</p>
                        <label class="mt-2 grid gap-1">
                            <span class="text-xs text-slate-400">Nickname</span>
                            <input type="text" value="" data-register-new maxlength="255" class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                        </label>

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <button type="button" data-register-new-add class="type-label border border-cyan-400/70 bg-cyan-400/10 px-2.5 py-1.5 text-[9px] text-cyan-100 transition hover:bg-cyan-400/18">
                                Add New Nickname
                            </button>
                            <p class="text-[11px] text-slate-500">New nicknames auto-create a user profile and player record.</p>
                        </div>
                    </section>
                </div>

                <section class="border border-slate-800/80 bg-slate-950/55 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="type-title text-[13px] text-fuchsia-100">Selected Players</p>
                        <span data-register-count class="type-label text-[8px] text-slate-500">{{ $oldSelectedNicknames->count() }} selected</span>
                    </div>

                    <div data-register-selected class="mt-2 min-h-[11rem] max-h-[15rem] space-y-1.5 overflow-y-auto no-scrollbar border border-dashed border-slate-800 bg-slate-950/40 p-2"></div>
                </section>
            </div>

            @if ($errors->has('nickname') || $errors->has('selected_nicknames') || $errors->has('selected_nicknames.*'))
                <p class="text-sm text-rose-300">
                    {{ $errors->first('nickname') ?: $errors->first('selected_nicknames') ?: $errors->first('selected_nicknames.*') }}
                </p>
            @endif

            <div data-register-hidden-inputs>
                @foreach ($oldSelectedNicknames as $nickname)
                    <input type="hidden" name="selected_nicknames[]" value="{{ $nickname }}">
                @endforeach
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-800/80 pt-3">
                <button type="button" data-register-modal-close class="type-label border border-slate-700 px-3 py-1.5 text-[9px] text-slate-100 transition hover:border-slate-500 hover:text-white">
                    Cancel
                </button>
                <button type="submit" data-register-submit @disabled($oldSelectedNicknames->isEmpty()) class="type-label border border-amber-400/70 bg-amber-400/12 px-3 py-1.5 text-[9px] text-amber-100 transition hover:bg-amber-400/20 disabled:cursor-not-allowed disabled:border-slate-800 disabled:bg-slate-900 disabled:text-slate-500">
                    Confirm Players
                </button>
            </div>
        </form>
    </div>
</div>
