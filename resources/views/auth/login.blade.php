<!DOCTYPE html>
<html lang="en" class="h-full bg-[#f5f8f6]">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Sign in · KIKIMO POS</title>
       <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>
    <!-- @vite('resources/js/app.js') -->
</head>
<body class="flex min-h-full items-center justify-center bg-[#f5f8f6] px-4 py-10">
    <main class="grid w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-xl shadow-emerald-950/10 md:min-h-[590px] md:grid-cols-2">
        <section class="relative hidden flex-col justify-between overflow-hidden bg-[#0a2a1d] p-10 text-white md:flex">
            <div class="absolute -right-24 -top-20 h-80 w-80 rounded-full border-[48px] border-white/5"></div>
            <div class="absolute -bottom-28 -left-20 h-80 w-80 rounded-full border-[48px] border-[#79c143]/10"></div>
            <a href="/" class="relative flex items-center gap-3"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#79c143] text-xl font-black text-[#0a2a1d]">K</span><span class="font-bold tracking-[0.2em]">KIKIMO POS</span></a>
            <div class="relative max-w-sm"><p class="mb-4 text-xs font-bold uppercase tracking-[0.2em] text-[#9bd764]">Business, in sync</p><h1 class="text-4xl font-semibold leading-tight tracking-tight">Every sale.<br>One clear picture.</h1><p class="mt-5 leading-7 text-emerald-50/65">Your point of sale, your team, your numbers — all in one place.</p></div>
            <p class="relative text-xs text-emerald-50/40">Secure access for your business team.</p>
        </section>
        <section class="flex items-center px-6 py-10 sm:px-12">
            <div class="mx-auto w-full max-w-sm">
                <div class="mb-8 md:hidden"><span class="font-bold tracking-[0.2em] text-[#144832]">KIKIMO POS</span></div>
                <p class="text-sm font-semibold text-[#01553d]">WELCOME BACK</p><h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Sign in to your account</h2><p class="mt-2 text-sm text-slate-500">Enter your business account credentials.</p>
                @if(session('status'))<div class="mt-5 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif
                @if($errors->any())<div class="mt-5 rounded-lg bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <div><label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email address</label><input id="email" name="email" type="email" autocomplete="username" required value="{{ old('email') }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#2b805b] focus:ring-4 focus:ring-emerald-900/5" placeholder="you@business.com"></div>
                    <div><div class="mb-1.5 flex justify-between"><label for="password" class="text-sm font-medium text-slate-700">Password</label><a href="{{ route('password.request') }}" class="text-xs font-semibold text-[#01553d] hover:underline">Forgot password?</a></div><input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-[#2b805b] focus:ring-4 focus:ring-emerald-900/5" placeholder="Enter your password"></div>
                    <label class="flex items-center gap-2 text-sm text-slate-600"><input name="remember" type="checkbox" value="1" class="rounded border-slate-300 text-[#01553d] focus:ring-[#01553d]">Remember me</label>
                    <button class="w-full rounded-xl bg-[#01553d] px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-900/15 transition hover:bg-[#0a2a1d] focus:outline-none focus:ring-4 focus:ring-emerald-900/20">Sign in</button>
                </form>
                <p class="mt-8 text-center text-xs text-slate-400">Need an account? Ask your business owner for access.</p>
            </div>
        </section>
    </main>
</body>
</html>
