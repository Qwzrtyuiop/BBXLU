@props([
    'title' => 'BBXLU',
    'fullBleed' => false,
    'hideFooter' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=oxanium:400,500,600,700,800&family=exo-2:400,500,600,700&family=orbitron:500,700&family=rajdhani:500,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div class="fixed inset-0 -z-10 bg-[radial-gradient(circle_at_20%_20%,rgba(251,191,36,0.14),transparent_28%),radial-gradient(circle_at_82%_8%,rgba(14,165,233,0.13),transparent_24%),linear-gradient(180deg,#020617_0%,#0b1120_55%,#0a0f1c_100%)]"></div>
    <div class="clash-flash clash-flash-left hidden lg:block" aria-hidden="true"></div>
    <div class="clash-flash clash-flash-right hidden lg:block" aria-hidden="true"></div>
    <div class="clash-impact hidden lg:block" aria-hidden="true"></div>
    <img
        src="{{ asset('images/impact.png') }}"
        alt=""
        aria-hidden="true"
        class="clash-impact-sprite clash-impact-sprite-a hidden lg:block"
    />
    <img
        src="{{ asset('images/impact2.png') }}"
        alt=""
        aria-hidden="true"
        class="clash-impact-sprite clash-impact-sprite-b hidden lg:block"
    />
    <img
        src="{{ asset('images/impact3.jpg') }}"
        alt=""
        aria-hidden="true"
        class="clash-impact-sprite clash-impact-sprite-c hidden lg:block"
    />
    {{-- Previous side positions (for quick restore):
         wiro: left-0 top-1/2 -translate-x-1/2 -translate-y-1/2
         dranb: right-0 top-1/2 translate-x-1/2 -translate-y-1/2
    --}}
    <div
        aria-hidden="true"
        class="clash-blade clash-blade-left hidden w-[clamp(22rem,34vw,36rem)] lg:block"
    >
        <img
            src="{{ asset('images/wiro.png') }}"
            alt=""
            class="clash-blade-art h-full w-full"
        />
    </div>
    <div
        aria-hidden="true"
        class="clash-blade clash-blade-right hidden w-[clamp(22rem,34vw,36rem)] lg:block"
    >
        <img
            src="{{ asset('images/dranb.png') }}"
            alt=""
            class="clash-blade-art h-full w-full"
        />
    </div>

    <header class="sticky top-0 z-30 border-b border-slate-800/70 bg-slate-950/80 backdrop-blur-xl">
        <div class="w-full px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                    <span class="inline-block h-2.5 w-2.5 rounded-none bg-amber-400"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-200 sm:text-sm sm:tracking-[0.26em]">ELYU BladerHub</span>
                </a>
                <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:justify-end">
                    <a href="{{ route('home') }}#players" class="flex-1 rounded-none border border-slate-700 px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-slate-200 hover:border-amber-500 hover:text-amber-200 sm:flex-none">
                        Players
                    </a>
                    <a href="{{ route('home') }}#register" class="flex-1 rounded-none border border-slate-700 px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-slate-200 hover:border-amber-500 hover:text-amber-200 sm:flex-none">
                        Register
                    </a>
                    <a href="{{ route('login') }}" class="flex-1 rounded-none border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-amber-200 hover:bg-amber-500/20 sm:flex-none">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="{{ $fullBleed ? 'w-full px-0 py-0' : 'mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8' }}">
        {{ $slot }}
    </main>

    @unless ($hideFooter)
        <footer class="border-t border-slate-800/70 bg-slate-950/70">
            <div class="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-center gap-3 px-4 py-4 text-xs text-slate-400 sm:justify-between sm:px-6 lg:px-8">
                <p class="text-center sm:text-left">&copy; 2026 La Union Bladers +</p>
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/agoo.png') }}" alt="Agoo icon" title="Agoo Bladers" class="h-6 w-6 object-contain" />
                    <img src="{{ asset('images/kimat.png') }}" alt="Kimat icon" title="Doc Kimat" class="h-6 w-6 object-contain" />
                    <img src="{{ asset('images/lu.png') }}" alt="La Union icon" title="La Union Bladers" class="h-6 w-6 object-contain" />
                </div>
            </div>
        </footer>
    @endunless
</body>
</html>
