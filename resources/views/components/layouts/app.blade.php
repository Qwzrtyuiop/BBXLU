<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'BBXLU' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=oxanium:400,500,600,700,800&family=exo-2:400,500,600,700&family=orbitron:500,700&family=rajdhani:500,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-900 via-slate-950 to-black text-slate-100 antialiased">
    @php
        $isFullScreen = (bool) ($fullScreen ?? false);
        $hideTopSelectors = (bool) ($hideTopSelectors ?? false);
        $hideFrameHeader = (bool) ($hideFrameHeader ?? false);
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
        $containerClasses = $isFullScreen
            ? 'w-full px-3 pb-3 pt-3 sm:px-4 lg:px-5'
            : 'mx-auto max-w-6xl px-4 pb-12 pt-8 sm:px-6 lg:px-8';
        $headerClasses = $isFullScreen
            ? 'mb-3 rounded-xl border border-amber-400/25 bg-slate-900/60 p-3 backdrop-blur'
            : 'mb-8 rounded-2xl border border-amber-400/30 bg-slate-900/60 p-4 backdrop-blur';
        $kickerClasses = $isFullScreen
            ? 'text-[10px] uppercase tracking-[0.2em] text-amber-300'
            : 'text-xs uppercase tracking-[0.2em] text-amber-300';
        $titleClasses = $isFullScreen
            ? 'text-lg font-bold text-amber-100'
            : 'text-2xl font-bold text-amber-100';
        $navClasses = $isFullScreen
            ? 'flex flex-wrap items-center gap-1.5'
            : 'flex flex-wrap items-center gap-2';
        $navLinkClasses = $isFullScreen
            ? 'rounded-md border border-slate-700 px-2.5 py-1.5 text-xs font-medium hover:border-amber-400 hover:text-amber-200'
            : 'rounded-lg border border-slate-700 px-3 py-2 text-sm font-medium hover:border-amber-400 hover:text-amber-200';
        $userBadgeClasses = $isFullScreen
            ? 'rounded-md border border-slate-700 px-2.5 py-1.5 text-xs text-slate-300'
            : 'rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300';
        $logoutButtonClasses = $isFullScreen
            ? 'rounded-md border border-rose-500/60 px-2.5 py-1.5 text-xs font-medium text-rose-200 hover:bg-rose-500/10'
            : 'rounded-lg border border-rose-500/60 px-3 py-2 text-sm font-medium text-rose-200 hover:bg-rose-500/10';
        $statusClasses = $isFullScreen
            ? 'mb-3 rounded-xl border border-emerald-500/40 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-200'
            : 'mb-6 rounded-xl border border-emerald-500/40 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-200';
        $errorClasses = $isFullScreen
            ? 'mb-3 rounded-xl border border-rose-500/40 bg-rose-900/30 px-4 py-3 text-sm text-rose-200'
            : 'mb-6 rounded-xl border border-rose-500/40 bg-rose-900/30 px-4 py-3 text-sm text-rose-200';
        $mainClasses = ! $isFullScreen
            ? ''
            : ($hideFrameHeader ? 'min-h-[calc(100svh-5.25rem)]' : 'min-h-[calc(100svh-8.8rem)]');
    @endphp

    <div class="{{ $containerClasses }}">
        @unless ($hideFrameHeader)
            <header class="{{ $headerClasses }}">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="{{ $kickerClasses }}">BBXLU Event Hub</p>
                        <h1 class="{{ $titleClasses }}">Tournament Control Panel</h1>
                    </div>
                    @if (! $hideTopSelectors)
                        <nav class="{{ $navClasses }}">
                            @if ($isAdmin)
                                <a href="{{ route('dashboard') }}" class="{{ $navLinkClasses }}">Dashboard</a>
                                <a href="{{ route('dashboard', ['panel' => 'events']) }}" class="{{ $navLinkClasses }}">Events</a>
                                <a href="{{ route('dashboard', ['panel' => 'players']) }}" class="{{ $navLinkClasses }}">Players</a>
                            @else
                                <a href="{{ route('user.dashboard') }}" class="{{ $navLinkClasses }}">User Dashboard</a>
                                <a href="{{ route('home') }}" class="{{ $navLinkClasses }}">Home</a>
                            @endif
                            <span class="{{ $userBadgeClasses }}">
                                {{ auth()->user()->nickname }}
                            </span>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="{{ $logoutButtonClasses }}">
                                    Logout
                                </button>
                            </form>
                        </nav>
                    @endif
                </div>
            </header>
        @endunless

        @if (session('status'))
            <div class="{{ $statusClasses }}">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="{{ $errorClasses }}">
                <p class="mb-2 font-semibold">Please fix the following:</p>
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main class="{{ $mainClasses }}">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
