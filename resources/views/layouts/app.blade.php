<!DOCTYPE html>
<html lang="en" class="h-full bg-[#f5f8f6]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · KIKIMO POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full text-slate-800 antialiased">
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-950/40 lg:hidden"></div>
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-[#0a2a1d] text-white transition-transform duration-200 lg:translate-x-0">
        <a href="{{ route('dashboard') }}" class="flex h-[76px] items-center gap-3 border-b border-white/10 px-6">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#79c143] text-lg font-black text-[#0a2a1d]">K</span>
            <span><span class="block text-sm font-bold tracking-[0.18em]">KIKIMO</span><span class="text-[11px] font-medium tracking-[0.2em] text-emerald-100/60">POS MANAGEMENT</span></span>
        </a>
        <div class="mx-4 mt-5 rounded-xl border border-white/10 bg-white/5 px-3 py-3">
            <p class="truncate text-sm font-semibold">{{ auth()->user()->business->name }}</p>
            <p class="mt-1 truncate text-xs text-emerald-100/60">{{ auth()->user()->business->business_code }}</p>
        </div>
        <nav class="mt-5 flex-1 space-y-1 overflow-y-auto px-3 pb-5">
            @php($navigation = [
                ['Dashboard', 'dashboard', 'dashboard'],
                ['Sales', 'sales.index', 'sales.*'],
                ['Products', 'products.index', 'products.*'],
                ['Cashiers', 'cashiers.index', 'cashiers.*'],
                ['POS terminals', 'terminals.index', 'terminals.*'],
                ['Sync history', 'sync.index', 'sync.*'],
                ['Reports', 'reports.index', 'reports.*'],
            ])
            @foreach($navigation as [$label, $route, $active])
                <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs($active) ? 'bg-[#144832] text-white shadow-sm' : 'text-emerald-50/70 hover:bg-white/10 hover:text-white' }}">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ request()->routeIs($active) ? 'bg-white/10 text-[#a6e06f]' : 'text-emerald-100/60' }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            @if($route === 'dashboard')<path d="M3 13h8V3H3v10Zm0 8h8v-5H3v5Zm10 0h8V11h-8v10Zm0-18v5h8V3h-8Z" />
                            @elseif($route === 'sales.index')<path d="M4 5h16M4 10h16M4 15h10M4 20h7" /><circle cx="18" cy="17" r="3" />
                            @elseif($route === 'products.index')<path d="m12 3 9 5-9 5-9-5 9-5Z" /><path d="m3 12 9 5 9-5M3 16l9 5 9-5" />
                            @elseif($route === 'cashiers.index')<circle cx="9" cy="8" r="4" /><path d="M2 21a7 7 0 0 1 14 0m3-12a4 4 0 0 1 0 8m1 4a6 6 0 0 0-4-5.7" />
                            @elseif($route === 'terminals.index')<rect x="4" y="3" width="16" height="14" rx="2" /><path d="M8 21h8m-4-4v4" />
                            @elseif($route === 'sync.index')<path d="M20 7v5h-5M4 17v-5h5" /><path d="M5.6 9a7 7 0 0 1 11.6-2L20 12M4 12l2.8 5a7 7 0 0 0 11.6-2" />
                            @else<path d="M3 3v18h18M7 14l4-4 4 3 6-7" />
                            @endif
                        </svg>
                    </span>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
            @if(auth()->user()->isAdministrator())
                <div class="px-3 pb-1 pt-6 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-100/40">Workspace</div>
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('users.*') ? 'bg-[#144832] text-white' : 'text-emerald-50/70 hover:bg-white/10 hover:text-white' }}">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg text-emerald-100/60"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0m5-12v6m-3-3h6"/></svg></span>
                    Team members
                </a>
                <a href="{{ route('settings.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('settings.*') ? 'bg-[#144832] text-white' : 'text-emerald-50/70 hover:bg-white/10 hover:text-white' }}">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg text-emerald-100/60"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/><path d="m19.4 15 1.4 1.1-1.4 2.4-1.7-.7a8 8 0 0 1-1.7 1l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.7-1l-1.7.7-1.4-2.4L7.2 15a8 8 0 0 1 0-2l-1.4-1.1 1.4-2.4 1.7.7a8 8 0 0 1 1.7-1l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.7 1l1.7-.7 1.4 2.4-1.4 1.1a8 8 0 0 1 0 2Z"/></svg></span>
                    Settings
                </a>
            @endif
            <div class="px-3 pb-1 pt-6 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-100/40">Account</div>
            <a href="{{ route('account.password.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('account.*') ? 'bg-[#144832] text-white' : 'text-emerald-50/70 hover:bg-white/10 hover:text-white' }}">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg text-emerald-100/60"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3m-4 5v2"/></svg></span>
                Change password
            </a>
        </nav>
        <div class="border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#79c143]/20 text-sm font-bold text-[#b8ed8c]">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold">{{ auth()->user()->name }}</span><span class="block truncate text-xs capitalize text-emerald-100/50">{{ auth()->user()->role }}</span></span>
                <form method="POST" action="{{ route('logout') }}">@csrf<button title="Sign out" class="rounded-lg p-2 text-emerald-100/60 hover:bg-white/10 hover:text-white"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/></svg></button></form>
            </div>
        </div>
    </aside>
    <div id="app-shell" class="min-h-screen transition-[padding] duration-200 lg:pl-64">
        <header class="sticky top-0 z-20 flex h-[76px] items-center justify-between border-b border-slate-200/80 bg-white/90 px-4 backdrop-blur md:px-8">
            <div class="flex items-center gap-3">
                <button id="sidebar-toggle" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100" aria-label="Toggle navigation" aria-expanded="true" aria-controls="sidebar"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
                <div><p class="text-xs font-medium text-slate-400">{{ now()->format('l, F j, Y') }}</p><p class="text-sm font-semibold text-slate-700">{{ auth()->user()->business->name }}</p></div>
            </div>
            <div class="flex items-center gap-3">
                @php($failedSyncCount = \App\Models\DesktopPosSyncLog::query()->whereIn('machine_id', auth()->user()->business->terminals()->select('machine_id'))->where('status', 'failed')->count())
                <a href="{{ route('sync.index', ['status' => 'failed']) }}" class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-[#01553d]" aria-label="{{ $failedSyncCount }} failed sync notifications" title="{{ $failedSyncCount }} failed synchronization(s)">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 12h4"/></svg>
                    @if($failedSyncCount > 0)<span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>@endif
                </a>
                <span class="hidden rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold capitalize text-emerald-800 sm:inline-flex">{{ auth()->user()->role }}</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#144832] text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
        </header>
        <main class="mx-auto w-full max-w-[1600px] px-4 py-6 md:px-8 md:py-8">
            @include('partials.flash')
            @yield('content')
        </main>
        <footer class="px-8 pb-6 text-center text-xs text-slate-400">KIKIMO POS · Secure business management</footer>
    </div>
</body>
</html>
