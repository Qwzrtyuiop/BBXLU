<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | BBXLU</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=orbitron:400,500,600,700,800,900&family=rajdhani:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-900 via-slate-950 to-black text-slate-100 antialiased">
    <div class="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-4 py-10 sm:px-6">
        <section class="grid w-full max-w-5xl overflow-hidden rounded-[1.75rem] border border-amber-400/35 bg-slate-900/78 shadow-[0_28px_70px_rgba(120,53,15,0.25)] backdrop-blur xl:grid-cols-[minmax(0,1.05fr)_24rem]">
            <div class="border-b border-slate-800/80 bg-[linear-gradient(145deg,rgba(8,47,73,0.94)_0%,rgba(15,23,42,0.98)_100%)] p-8 xl:border-b-0 xl:border-r">
                <p class="text-xs uppercase tracking-[0.22em] text-cyan-300">BBXLU Access</p>
                <h1 class="mt-3 text-4xl font-bold uppercase tracking-[0.08em] text-amber-100">Login</h1>
                <p class="mt-4 max-w-xl text-sm leading-6 text-slate-300">
                    Sign in with your nickname or email. Admin accounts go to the tournament control panel, and player accounts go to the user dashboard.
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <article class="rounded-2xl border border-cyan-400/25 bg-slate-950/45 p-4">
                        <p class="text-[10px] uppercase tracking-[0.18em] text-cyan-300/75">Admin</p>
                        <p class="mt-2 text-sm text-slate-300">Manage events, brackets, rankings, and live workspace tools.</p>
                    </article>
                    <article class="rounded-2xl border border-amber-400/25 bg-slate-950/45 p-4">
                        <p class="text-[10px] uppercase tracking-[0.18em] text-amber-300/75">Player</p>
                        <p class="mt-2 text-sm text-slate-300">Check your joined events, results, and claimed account details.</p>
                    </article>
                    <article class="rounded-2xl border border-fuchsia-400/25 bg-slate-950/45 p-4">
                        <p class="text-[10px] uppercase tracking-[0.18em] text-fuchsia-300/75">Claim</p>
                        <p class="mt-2 text-sm text-slate-300">If an admin already registered you, claim that account during sign up.</p>
                    </article>
                </div>
            </div>

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

                <form action="{{ route('login.store') }}" method="POST" @class(['grid gap-4', 'mt-5' => $errors->any()])>
                    @csrf
                    <label class="grid gap-1.5">
                        <span class="text-sm text-slate-300">Nickname or Email</span>
                        <input
                            name="login"
                            value="{{ old('login') }}"
                            required
                            autofocus
                            class="rounded-xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-slate-100 focus:border-amber-500 focus:outline-none"
                        >
                    </label>
                    <label class="grid gap-1.5">
                        <span class="text-sm text-slate-300">Password</span>
                        <input
                            type="password"
                            name="password"
                            required
                            class="rounded-xl border border-slate-700 bg-slate-950/70 px-3 py-3 text-slate-100 focus:border-amber-500 focus:outline-none"
                        >
                    </label>

                    <button class="mt-2 rounded-xl border border-amber-500/70 bg-amber-500/10 px-4 py-3 text-sm font-semibold uppercase tracking-[0.14em] text-amber-100 transition hover:bg-amber-500/20">
                        Sign In
                    </button>
                </form>

                <div class="mt-6 rounded-2xl border border-slate-800 bg-slate-950/45 p-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Need an account?</p>
                    <p class="mt-2 text-sm text-slate-300">Create a player account or claim an unclaimed registration from the sign-up page.</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="inline-flex rounded-xl border border-cyan-400/55 bg-cyan-400/10 px-4 py-2.5 text-sm font-semibold uppercase tracking-[0.14em] text-cyan-100 transition hover:bg-cyan-400/18">
                            Register
                        </a>
                        <a href="{{ route('home') }}" class="inline-flex rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-semibold uppercase tracking-[0.14em] text-slate-100 transition hover:border-amber-400 hover:text-amber-200">
                            Back To Home
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
