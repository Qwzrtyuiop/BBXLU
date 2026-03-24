<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | BBXLU</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=orbitron:400,500,600,700,800,900&family=rajdhani:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-900 via-slate-950 to-black text-slate-100 antialiased">
    <div class="mx-auto flex min-h-screen max-w-5xl items-center justify-center px-4 py-10 sm:px-6">
        <section class="w-full max-w-md rounded-2xl border border-amber-400/40 bg-slate-900/80 p-6 shadow-2xl shadow-amber-900/20 backdrop-blur">
            <p class="text-xs uppercase tracking-[0.2em] text-amber-300">BBXLU</p>
            <h1 class="mt-2 text-3xl font-bold text-amber-100">Admin Login</h1>
            <p class="mt-2 text-sm text-slate-400">Use your admin nickname or email plus password.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-rose-500/40 bg-rose-900/30 px-4 py-3 text-sm text-rose-200">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.store') }}" method="POST" class="mt-6 grid gap-4">
                @csrf
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Nickname or Email</span>
                    <input
                        name="login"
                        value="{{ old('login') }}"
                        required
                        autofocus
                        class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none"
                    >
                </label>
                <label class="grid gap-1">
                    <span class="text-sm text-slate-300">Password</span>
                    <input
                        type="password"
                        name="password"
                        required
                        class="rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-slate-100 focus:border-amber-500 focus:outline-none"
                    >
                </label>

                <button class="mt-2 rounded-lg border border-amber-500/70 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-100 hover:bg-amber-500/20">
                    Sign In
                </button>
            </form>
        </section>
    </div>
</body>
</html>
