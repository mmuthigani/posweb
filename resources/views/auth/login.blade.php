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
    <script>
        const loginColorThemes = ['green', 'blue', 'violet', 'amber', 'rose', 'teal', 'indigo', 'red', 'slate', 'cyan'];
        const savedLoginTheme = localStorage.getItem('kikimo-color-theme');
        document.documentElement.dataset.colorTheme = loginColorThemes.includes(savedLoginTheme) ? savedLoginTheme : 'green';
    </script>
    <style>
        html[data-color-theme="green"] { --login-primary: #01553d; --login-secondary: #79c143; --login-dark: #0a2a1d; --login-mid: #046a4c; --login-highlight: #9bd764; }
        html[data-color-theme="blue"] { --login-primary: #2563eb; --login-secondary: #60a5fa; --login-dark: #102a43; --login-mid: #1d4f73; --login-highlight: #bfdbfe; }
        html[data-color-theme="violet"] { --login-primary: #7c3aed; --login-secondary: #c4b5fd; --login-dark: #241044; --login-mid: #4c1d95; --login-highlight: #ddd6fe; }
        html[data-color-theme="amber"] { --login-primary: #b45309; --login-secondary: #fbbf24; --login-dark: #3a1f0c; --login-mid: #854d0e; --login-highlight: #fde68a; }
        html[data-color-theme="rose"] { --login-primary: #e11d48; --login-secondary: #fb7185; --login-dark: #4c0519; --login-mid: #9f1239; --login-highlight: #fda4af; }
        html[data-color-theme="teal"] { --login-primary: #0f766e; --login-secondary: #2dd4bf; --login-dark: #042f2e; --login-mid: #115e59; --login-highlight: #5eead4; }
        html[data-color-theme="indigo"] { --login-primary: #4f46e5; --login-secondary: #818cf8; --login-dark: #1e1b4b; --login-mid: #3730a3; --login-highlight: #a5b4fc; }
        html[data-color-theme="red"] { --login-primary: #dc2626; --login-secondary: #f87171; --login-dark: #450a0a; --login-mid: #991b1b; --login-highlight: #fca5a5; }
        html[data-color-theme="slate"] { --login-primary: #475569; --login-secondary: #94a3b8; --login-dark: #0f172a; --login-mid: #334155; --login-highlight: #cbd5e1; }
        html[data-color-theme="cyan"] { --login-primary: #0891b2; --login-secondary: #22d3ee; --login-dark: #083344; --login-mid: #0e7490; --login-highlight: #67e8f9; }
        .login-theme-primary { color: var(--login-primary) !important; }
        .login-theme-primary-bg { background-color: var(--login-primary) !important; }
        .login-theme-secondary-bg { background-color: var(--login-secondary) !important; }
        .login-theme-dark-bg { background-color: var(--login-dark) !important; }
        .login-theme-dark-text { color: var(--login-dark) !important; }
        .login-theme-mid-text { color: var(--login-mid) !important; }
        .login-theme-highlight { color: var(--login-highlight) !important; }
        .login-theme-focus:focus { border-color: var(--login-primary) !important; --tw-ring-color: color-mix(in srgb, var(--login-primary) 10%, transparent) !important; }
        .login-theme-ring:focus { --tw-ring-color: color-mix(in srgb, var(--login-primary) 20%, transparent) !important; }
    </style>

</head>
<body class="flex min-h-full items-center justify-center bg-[#f5f8f6] px-4 py-10">
    <main class="grid w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-xl shadow-emerald-950/10 md:min-h-[590px] md:grid-cols-2">
        <section class="relative hidden flex-col justify-between overflow-hidden login-theme-dark-bg p-10 text-white md:flex">
            <div class="absolute -right-24 -top-20 h-80 w-80 rounded-full border-[48px] border-white/5"></div>
            <div class="absolute -bottom-28 -left-20 h-80 w-80 rounded-full border-[48px] login-theme-secondary-bg opacity-10"></div>
            <a href="/" class="relative flex items-center gap-3"><span class="flex h-11 w-11 items-center justify-center rounded-xl login-theme-secondary-bg text-xl font-black login-theme-dark-text">K</span><span class="font-bold tracking-[0.2em]">KIKIMO POS</span></a>
            <div class="relative max-w-sm"><p class="mb-4 text-xs font-bold uppercase tracking-[0.2em] login-theme-highlight">Business, in sync</p><h1 class="text-4xl font-semibold leading-tight tracking-tight">Every sale.<br>One clear picture.</h1><p class="mt-5 leading-7 text-emerald-50/65">Your point of sale, your team, your numbers — all in one place.</p></div>
            <p class="relative text-xs text-emerald-50/40">Secure access for your business team.</p>
        </section>
        <section class="flex items-center px-6 py-10 sm:px-12">
            <div class="mx-auto w-full max-w-sm">
                <div class="mb-8 md:hidden"><span class="font-bold tracking-[0.2em] login-theme-mid-text">KIKIMO POS</span></div>
                <p class="text-sm font-semibold login-theme-primary">WELCOME BACK</p><h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Sign in to your account</h2><p class="mt-2 text-sm text-slate-500">Enter your business account credentials.</p>
                @if(session('status'))<div class="mt-5 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif
                @if($errors->any())<div class="mt-5 rounded-lg bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <div><label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email address</label><input id="email" name="email" type="email" autocomplete="username" required value="{{ old('email') }}" class="login-theme-focus w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:ring-4" placeholder="you@business.com"></div>
                    <div><div class="mb-1.5 flex justify-between"><label for="password" class="text-sm font-medium text-slate-700">Password</label><a href="{{ route('password.request') }}" class="text-xs font-semibold login-theme-primary hover:underline">Forgot password?</a></div><input id="password" name="password" type="password" autocomplete="current-password" required class="login-theme-focus w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:ring-4" placeholder="Enter your password"></div>
                    <label class="flex items-center gap-2 text-sm text-slate-600"><input name="remember" type="checkbox" value="1" class="login-theme-primary rounded border-slate-300 focus:ring-2 login-theme-ring">Remember me</label>
                    <button class="login-theme-primary-bg w-full rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-900/15 transition hover:brightness-90 focus:outline-none focus:ring-4 login-theme-ring">Sign in</button>
                </form>
                <p class="mt-8 text-center text-xs text-slate-400">Need an account? Ask your business owner for access.</p>
            </div>
        </section>
    </main>
</body>
</html>
