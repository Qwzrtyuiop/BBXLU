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
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div class="fixed inset-0 -z-10 bg-[radial-gradient(circle_at_20%_20%,rgba(251,191,36,0.14),transparent_28%),radial-gradient(circle_at_82%_8%,rgba(14,165,233,0.13),transparent_24%),linear-gradient(180deg,#020617_0%,#0b1120_55%,#0a0f1c_100%)]"></div>

    <header class="sticky top-0 z-30 border-b border-slate-800/70 bg-slate-950/80 backdrop-blur-xl">
        <div class="w-full px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                    <span class="inline-block h-2.5 w-2.5 rounded-none bg-amber-400"></span>
                    <span class="text-sm font-semibold uppercase tracking-[0.26em] text-amber-200">ELYU BladerHub</span>
                </a>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('home') }}#players" class="rounded-none border border-slate-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-200 hover:border-amber-500 hover:text-amber-200">
                        Players
                    </a>
                    <a href="{{ route('home') }}#register" class="rounded-none border border-slate-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-200 hover:border-amber-500 hover:text-amber-200">
                        Register
                    </a>
                    <a href="{{ route('login') }}" class="rounded-none border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-amber-200 hover:bg-amber-500/20">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-800/70 bg-slate-950/70">
        <div class="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-4 text-xs text-slate-400 sm:px-6 lg:px-8">
            <p>&copy; 2026 La Union Bladers +</p>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/agoo.png') }}" alt="Agoo icon" title="Agoo Bladers" class="h-6 w-6 object-contain" />
                <img src="{{ asset('images/kimat.png') }}" alt="Kimat icon" title="Doc Kimat" class="h-6 w-6 object-contain" />
                <img src="{{ asset('images/lu.png') }}" alt="La Union icon" title="La Union Bladers" class="h-6 w-6 object-contain" />
            </div>
        </div>
    </footer>
</body>
</html>
