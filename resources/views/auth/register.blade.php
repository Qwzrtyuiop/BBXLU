<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | BBXLU</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=orbitron:400,500,600,700,800,900&family=rajdhani:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-900 via-slate-950 to-black text-slate-100 antialiased">
    @php
        $mode = old('mode', 'register');
        $claimAvailable = $unclaimedAccounts->isNotEmpty();
    @endphp

    <div class="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-4 py-10 sm:px-6">
        <section class="grid w-full max-w-5xl overflow-hidden rounded-[1.75rem] border border-cyan-400/30 bg-slate-900/78 shadow-[0_28px_70px_rgba(8,47,73,0.26)] backdrop-blur xl:grid-cols-[22rem_minmax(0,1fr)]">
            <aside class="border-b border-slate-800/80 bg-[linear-gradient(145deg,rgba(2,6,23,0.96)_0%,rgba(8,47,73,0.82)_100%)] p-8 xl:border-b-0 xl:border-r">
                <p class="text-xs uppercase tracking-[0.22em] text-cyan-300">BBXLU Join</p>
                <h1 class="mt-3 text-4xl font-bold uppercase tracking-[0.08em] text-amber-100">Register</h1>
                <p class="mt-4 text-sm leading-6 text-slate-300">
                    Create a new player account or claim an unclaimed account that was already created by the admin during event registration.
                </p>

                <div class="mt-8 space-y-4">
                    <article class="rounded-2xl border border-amber-400/20 bg-slate-950/45 p-4">
                        <p class="text-[10px] uppercase tracking-[0.18em] text-amber-300/75">New Registration</p>
                        <p class="mt-2 text-sm text-slate-300">Creates a claimed user account and player profile immediately. If the nickname already exists, use Claim Account instead.</p>
                    </article>
                    <article class="rounded-2xl border border-fuchsia-400/20 bg-slate-950/45 p-4">
                        <p class="text-[10px] uppercase tracking-[0.18em] text-fuchsia-300/75">Claim Account</p>
                        <p class="mt-2 text-sm text-slate-300">
                            {{ $claimAvailable ? 'Type the nickname that was already registered for you, then set your password and activate it.' : 'No unclaimed accounts are available right now.' }}
                        </p>
                    </article>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="inline-flex rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-semibold uppercase tracking-[0.14em] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">
                        Back To Login
                    </a>
                    <a href="{{ route('home') }}" class="inline-flex rounded-xl border border-cyan-400/55 bg-cyan-400/10 px-4 py-2.5 text-sm font-semibold uppercase tracking-[0.14em] text-cyan-100 transition hover:bg-cyan-400/18">
                        Back To Home
                    </a>
                </div>
            </aside>

            <div class="p-8">
                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-500/40 bg-rose-900/30 px-4 py-3 text-sm text-rose-200">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register.store') }}" method="POST" data-auth-register @class(['grid gap-5', 'mt-5' => $errors->any()])>
                    @csrf

                    <fieldset class="grid gap-3 sm:grid-cols-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="mode" value="register" class="peer sr-only" @checked($mode !== 'claim')>
                            <span class="flex h-full rounded-2xl border border-amber-400/45 bg-amber-500/10 px-4 py-4 text-sm font-semibold uppercase tracking-[0.16em] text-amber-100 transition peer-checked:border-amber-300 peer-checked:bg-amber-400/16">
                                New Registration
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="mode" value="claim" class="peer sr-only" @checked($mode === 'claim') @disabled(! $claimAvailable)>
                            <span class="flex h-full rounded-2xl border px-4 py-4 text-sm font-semibold uppercase tracking-[0.16em] transition {{ $claimAvailable ? 'border-fuchsia-400/45 bg-fuchsia-500/10 text-fuchsia-100 peer-checked:border-fuchsia-300 peer-checked:bg-fuchsia-400/16' : 'border-slate-800 bg-slate-950/40 text-slate-500' }}">
                                Claim Account
                            </span>
                        </label>
                    </fieldset>

                    <div data-register-mode-panel="register" class="{{ $mode === 'claim' ? 'hidden ' : '' }}grid gap-4">
                        <label class="grid gap-1.5">
                            <span class="text-sm text-slate-300">Nickname</span>
                            <input
                                type="text"
                                name="nickname"
                                value="{{ old('nickname') }}"
                                class="rounded-xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-slate-100 focus:border-amber-500 focus:outline-none"
                            >
                        </label>
                    </div>

                    <div data-register-mode-panel="claim" class="{{ $mode === 'claim' ? '' : 'hidden ' }}grid gap-4">
                        <label class="grid gap-1.5">
                            <span class="text-sm text-slate-300">Registered Nickname</span>
                            <input
                                type="text"
                                name="claim_nickname"
                                value="{{ old('claim_nickname') }}"
                                class="rounded-xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-slate-100 focus:border-amber-500 focus:outline-none"
                            >
                        </label>
                        <p class="text-xs text-slate-500">
                            {{ $claimAvailable ? 'The typed nickname must match an existing unclaimed account in the database.' : 'Claim mode is unavailable until an admin creates an unclaimed account for you.' }}
                        </p>
                    </div>

                    @if ($claimAvailable)
                        <p class="text-xs uppercase tracking-[0.16em] text-slate-500">
                            {{ $unclaimedAccounts->count() }} unclaimed account{{ $unclaimedAccounts->count() === 1 ? '' : 's' }} currently available
                        </p>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-1.5">
                            <span class="text-sm text-slate-300">Name</span>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                class="rounded-xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-slate-100 focus:border-amber-500 focus:outline-none"
                            >
                        </label>
                        <label class="grid gap-1.5">
                            <span class="text-sm text-slate-300">Email</span>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="rounded-xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-slate-100 focus:border-amber-500 focus:outline-none"
                            >
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-1.5">
                            <span class="text-sm text-slate-300">Password</span>
                            <input
                                type="password"
                                name="password"
                                required
                                class="rounded-xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-slate-100 focus:border-amber-500 focus:outline-none"
                            >
                        </label>
                        <label class="grid gap-1.5">
                            <span class="text-sm text-slate-300">Confirm Password</span>
                            <input
                                type="password"
                                name="password_confirmation"
                                required
                                class="rounded-xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-slate-100 focus:border-amber-500 focus:outline-none"
                            >
                        </label>
                    </div>

                    <button class="rounded-xl border border-cyan-400/60 bg-cyan-400/10 px-4 py-3 text-sm font-semibold uppercase tracking-[0.14em] text-cyan-100 transition hover:bg-cyan-400/18">
                        Continue
                    </button>
                </form>
            </div>
        </section>
    </div>
</body>
</html>
