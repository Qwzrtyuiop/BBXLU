<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'BBXLU' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=orbitron:400,500,600,700,800,900&family=rajdhani:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-900 via-slate-950 to-black text-slate-100 antialiased">
    <div class="mx-auto max-w-6xl px-4 pb-12 pt-8 sm:px-6 lg:px-8">
        <header class="mb-8 rounded-2xl border border-amber-400/30 bg-slate-900/60 p-4 backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-amber-300">BBXLU Event Hub</p>
                    <h1 class="text-2xl font-bold text-amber-100">Tournament Control Panel</h1>
                </div>
                <nav class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('dashboard') }}" class="rounded-lg border border-slate-700 px-3 py-2 text-sm font-medium hover:border-amber-400 hover:text-amber-200">Dashboard</a>
                    <a href="{{ route('events.index') }}" class="rounded-lg border border-slate-700 px-3 py-2 text-sm font-medium hover:border-amber-400 hover:text-amber-200">Events</a>
                    <a href="{{ route('players.index') }}" class="rounded-lg border border-slate-700 px-3 py-2 text-sm font-medium hover:border-amber-400 hover:text-amber-200">Players</a>
                    <span class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300">
                        {{ auth()->user()->nickname }}
                    </span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="rounded-lg border border-rose-500/60 px-3 py-2 text-sm font-medium text-rose-200 hover:bg-rose-500/10">
                            Logout
                        </button>
                    </form>
                </nav>
            </div>
        </header>

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-500/40 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-rose-500/40 bg-rose-900/30 px-4 py-3 text-sm text-rose-200">
                <p class="mb-2 font-semibold">Please fix the following:</p>
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main>
            {{ $slot }}
        </main>
    </div>
</body>
</html>
