<!DOCTYPE html>
<html lang="en" class="h-full bg-[#f5f8f6]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · KIKIMO POS</title>
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
        window.kikimoColorThemes = ['green', 'blue', 'violet', 'amber', 'rose', 'teal', 'indigo', 'red', 'slate', 'cyan'];
        const savedColorTheme = localStorage.getItem('kikimo-color-theme');
        document.documentElement.dataset.colorTheme = window.kikimoColorThemes.includes(savedColorTheme) ? savedColorTheme : 'green';
    </script>
    <style>
        html[data-color-theme="blue"] [class~="bg-[#01553d]"] { background-color: #2563eb !important; }
        html[data-color-theme="blue"] [class~="text-[#01553d]"],
        html[data-color-theme="blue"] [class~="hover:text-[#01553d]"]:hover { color: #2563eb !important; }
        html[data-color-theme="blue"] [class~="bg-[#0a2a1d]"],
        html[data-color-theme="blue"] [class~="hover:bg-[#0a2a1d]"]:hover { background-color: #102a43 !important; }
        html[data-color-theme="blue"] [class~="text-[#0a2a1d]"] { color: #102a43 !important; }
        html[data-color-theme="blue"] [class~="bg-[#144832]"] { background-color: #1d4f73 !important; }
        html[data-color-theme="blue"] [class~="bg-[#79c143]"] { background-color: #60a5fa !important; }
        html[data-color-theme="blue"] [class~="text-[#a6e06f]"],
        html[data-color-theme="blue"] [class~="text-[#b8ed8c]"] { color: #bfdbfe !important; }
        html[data-color-theme="blue"] [class~="focus:border-[#2b805b]"]:focus { border-color: #3b82f6 !important; }

        html[data-color-theme="violet"] [class~="bg-[#01553d]"] { background-color: #7c3aed !important; }
        html[data-color-theme="violet"] [class~="text-[#01553d]"],
        html[data-color-theme="violet"] [class~="hover:text-[#01553d]"]:hover { color: #7c3aed !important; }
        html[data-color-theme="violet"] [class~="bg-[#0a2a1d]"],
        html[data-color-theme="violet"] [class~="hover:bg-[#0a2a1d]"]:hover { background-color: #241044 !important; }
        html[data-color-theme="violet"] [class~="text-[#0a2a1d]"] { color: #241044 !important; }
        html[data-color-theme="violet"] [class~="bg-[#144832]"] { background-color: #4c1d95 !important; }
        html[data-color-theme="violet"] [class~="bg-[#79c143]"] { background-color: #c4b5fd !important; }
        html[data-color-theme="violet"] [class~="text-[#a6e06f]"],
        html[data-color-theme="violet"] [class~="text-[#b8ed8c]"] { color: #ddd6fe !important; }
        html[data-color-theme="violet"] [class~="focus:border-[#2b805b]"]:focus { border-color: #8b5cf6 !important; }

        html[data-color-theme="amber"] [class~="bg-[#01553d]"] { background-color: #b45309 !important; }
        html[data-color-theme="amber"] [class~="text-[#01553d]"],
        html[data-color-theme="amber"] [class~="hover:text-[#01553d]"]:hover { color: #b45309 !important; }
        html[data-color-theme="amber"] [class~="bg-[#0a2a1d]"],
        html[data-color-theme="amber"] [class~="hover:bg-[#0a2a1d]"]:hover { background-color: #3a1f0c !important; }
        html[data-color-theme="amber"] [class~="text-[#0a2a1d]"] { color: #3a1f0c !important; }
        html[data-color-theme="amber"] [class~="bg-[#144832]"] { background-color: #854d0e !important; }
        html[data-color-theme="amber"] [class~="bg-[#79c143]"] { background-color: #fbbf24 !important; }
        html[data-color-theme="amber"] [class~="text-[#a6e06f]"],
        html[data-color-theme="amber"] [class~="text-[#b8ed8c]"] { color: #fde68a !important; }
        html[data-color-theme="amber"] [class~="focus:border-[#2b805b]"]:focus { border-color: #d97706 !important; }

        html[data-color-theme="blue"] #dashboard-content [class~="from-[#01553d]"] { --tw-gradient-from: #2563eb var(--tw-gradient-from-position) !important; }
        html[data-color-theme="blue"] #dashboard-content [class~="via-[#014532]"],
        html[data-color-theme="blue"] #dashboard-content [class~="via-[#046a4c]"] { --tw-gradient-via: #1d4f73 var(--tw-gradient-via-position) !important; }
        html[data-color-theme="blue"] #dashboard-content [class~="to-[#0a2a1d]"] { --tw-gradient-to: #102a43 var(--tw-gradient-to-position) !important; }
        html[data-color-theme="blue"] #dashboard-content [class~="from-[#79c143]"] { --tw-gradient-from: #60a5fa var(--tw-gradient-from-position) !important; }
        html[data-color-theme="blue"] #dashboard-content [class~="to-[#79c143]"] { --tw-gradient-to: #60a5fa var(--tw-gradient-to-position) !important; }
        html[data-color-theme="blue"] #dashboard-content [class~="bg-emerald-50"],
        html[data-color-theme="blue"] #dashboard-content [class~="from-emerald-50"] { background-color: #eff6ff !important; --tw-gradient-from: #eff6ff var(--tw-gradient-from-position) !important; }
        html[data-color-theme="blue"] #dashboard-content [class~="bg-emerald-100"],
        html[data-color-theme="blue"] #dashboard-content [class~="to-emerald-100"] { background-color: #dbeafe !important; --tw-gradient-to: #dbeafe var(--tw-gradient-to-position) !important; }
        html[data-color-theme="blue"] #dashboard-content [class~="from-emerald-100"] { --tw-gradient-from: #dbeafe var(--tw-gradient-from-position) !important; }
        html[data-color-theme="blue"] #dashboard-content [class~="to-emerald-200"] { --tw-gradient-to: #bfdbfe var(--tw-gradient-to-position) !important; }
        html[data-color-theme="blue"] #dashboard-content [class~="hover:bg-emerald-50/40"]:hover { background-color: rgb(239 246 255 / 40%) !important; }

        html[data-color-theme="violet"] #dashboard-content [class~="from-[#01553d]"] { --tw-gradient-from: #7c3aed var(--tw-gradient-from-position) !important; }
        html[data-color-theme="violet"] #dashboard-content [class~="via-[#014532]"],
        html[data-color-theme="violet"] #dashboard-content [class~="via-[#046a4c]"] { --tw-gradient-via: #4c1d95 var(--tw-gradient-via-position) !important; }
        html[data-color-theme="violet"] #dashboard-content [class~="to-[#0a2a1d]"] { --tw-gradient-to: #241044 var(--tw-gradient-to-position) !important; }
        html[data-color-theme="violet"] #dashboard-content [class~="from-[#79c143]"] { --tw-gradient-from: #c4b5fd var(--tw-gradient-from-position) !important; }
        html[data-color-theme="violet"] #dashboard-content [class~="to-[#79c143]"] { --tw-gradient-to: #c4b5fd var(--tw-gradient-to-position) !important; }
        html[data-color-theme="violet"] #dashboard-content [class~="bg-emerald-50"],
        html[data-color-theme="violet"] #dashboard-content [class~="from-emerald-50"] { background-color: #f5f3ff !important; --tw-gradient-from: #f5f3ff var(--tw-gradient-from-position) !important; }
        html[data-color-theme="violet"] #dashboard-content [class~="bg-emerald-100"],
        html[data-color-theme="violet"] #dashboard-content [class~="to-emerald-100"] { background-color: #ede9fe !important; --tw-gradient-to: #ede9fe var(--tw-gradient-to-position) !important; }
        html[data-color-theme="violet"] #dashboard-content [class~="from-emerald-100"] { --tw-gradient-from: #ede9fe var(--tw-gradient-from-position) !important; }
        html[data-color-theme="violet"] #dashboard-content [class~="to-emerald-200"] { --tw-gradient-to: #ddd6fe var(--tw-gradient-to-position) !important; }
        html[data-color-theme="violet"] #dashboard-content [class~="hover:bg-emerald-50/40"]:hover { background-color: rgb(245 243 255 / 40%) !important; }

        html[data-color-theme="amber"] #dashboard-content [class~="from-[#01553d]"] { --tw-gradient-from: #b45309 var(--tw-gradient-from-position) !important; }
        html[data-color-theme="amber"] #dashboard-content [class~="via-[#014532]"],
        html[data-color-theme="amber"] #dashboard-content [class~="via-[#046a4c]"] { --tw-gradient-via: #854d0e var(--tw-gradient-via-position) !important; }
        html[data-color-theme="amber"] #dashboard-content [class~="to-[#0a2a1d]"] { --tw-gradient-to: #3a1f0c var(--tw-gradient-to-position) !important; }
        html[data-color-theme="amber"] #dashboard-content [class~="from-[#79c143]"] { --tw-gradient-from: #fbbf24 var(--tw-gradient-from-position) !important; }
        html[data-color-theme="amber"] #dashboard-content [class~="to-[#79c143]"] { --tw-gradient-to: #fbbf24 var(--tw-gradient-to-position) !important; }
        html[data-color-theme="amber"] #dashboard-content [class~="bg-emerald-50"],
        html[data-color-theme="amber"] #dashboard-content [class~="from-emerald-50"] { background-color: #fffbeb !important; --tw-gradient-from: #fffbeb var(--tw-gradient-from-position) !important; }
        html[data-color-theme="amber"] #dashboard-content [class~="bg-emerald-100"],
        html[data-color-theme="amber"] #dashboard-content [class~="to-emerald-100"] { background-color: #fef3c7 !important; --tw-gradient-to: #fef3c7 var(--tw-gradient-to-position) !important; }
        html[data-color-theme="amber"] #dashboard-content [class~="from-emerald-100"] { --tw-gradient-from: #fef3c7 var(--tw-gradient-from-position) !important; }
        html[data-color-theme="amber"] #dashboard-content [class~="to-emerald-200"] { --tw-gradient-to: #fde68a var(--tw-gradient-to-position) !important; }
        html[data-color-theme="amber"] #dashboard-content [class~="hover:bg-emerald-50/40"]:hover { background-color: rgb(255 251 235 / 40%) !important; }

        [data-color-theme-choice][aria-pressed="true"] { border-color: #01553d; box-shadow: 0 0 0 2px rgb(1 85 61 / 15%); }
        html[data-color-theme="blue"] [data-color-theme-choice][aria-pressed="true"] { border-color: #2563eb; box-shadow: 0 0 0 2px rgb(37 99 235 / 15%); }
        html[data-color-theme="violet"] [data-color-theme-choice][aria-pressed="true"] { border-color: #7c3aed; box-shadow: 0 0 0 2px rgb(124 58 237 / 15%); }
        html[data-color-theme="amber"] [data-color-theme-choice][aria-pressed="true"] { border-color: #b45309; box-shadow: 0 0 0 2px rgb(180 83 9 / 15%); }

        html[data-color-theme="green"] { --theme-primary: #01553d; --theme-secondary: #79c143; --theme-dark: #0a2a1d; --theme-mid: #046a4c; --theme-soft-50: #ecfdf5; --theme-soft-100: #d1fae5; --theme-soft-200: #a7f3d0; --theme-secondary-light: #a6e06f; --theme-secondary-lighter: #b8ed8c; }
        html[data-color-theme="blue"] { --theme-primary: #2563eb; --theme-secondary: #60a5fa; --theme-dark: #102a43; --theme-mid: #1d4f73; --theme-soft-50: #eff6ff; --theme-soft-100: #dbeafe; --theme-soft-200: #bfdbfe; --theme-secondary-light: #bfdbfe; --theme-secondary-lighter: #dbeafe; }
        html[data-color-theme="violet"] { --theme-primary: #7c3aed; --theme-secondary: #c4b5fd; --theme-dark: #241044; --theme-mid: #4c1d95; --theme-soft-50: #f5f3ff; --theme-soft-100: #ede9fe; --theme-soft-200: #ddd6fe; --theme-secondary-light: #ddd6fe; --theme-secondary-lighter: #ede9fe; }
        html[data-color-theme="amber"] { --theme-primary: #b45309; --theme-secondary: #fbbf24; --theme-dark: #3a1f0c; --theme-mid: #854d0e; --theme-soft-50: #fffbeb; --theme-soft-100: #fef3c7; --theme-soft-200: #fde68a; --theme-secondary-light: #fde68a; --theme-secondary-lighter: #fef3c7; }
        html[data-color-theme="rose"] { --theme-primary: #e11d48; --theme-secondary: #fb7185; --theme-dark: #4c0519; --theme-mid: #9f1239; --theme-soft-50: #fff1f2; --theme-soft-100: #ffe4e6; --theme-soft-200: #fecdd3; --theme-secondary-light: #fda4af; --theme-secondary-lighter: #fecdd3; }
        html[data-color-theme="teal"] { --theme-primary: #0f766e; --theme-secondary: #2dd4bf; --theme-dark: #042f2e; --theme-mid: #115e59; --theme-soft-50: #f0fdfa; --theme-soft-100: #ccfbf1; --theme-soft-200: #99f6e4; --theme-secondary-light: #5eead4; --theme-secondary-lighter: #99f6e4; }
        html[data-color-theme="indigo"] { --theme-primary: #4f46e5; --theme-secondary: #818cf8; --theme-dark: #1e1b4b; --theme-mid: #3730a3; --theme-soft-50: #eef2ff; --theme-soft-100: #e0e7ff; --theme-soft-200: #c7d2fe; --theme-secondary-light: #a5b4fc; --theme-secondary-lighter: #c7d2fe; }
        html[data-color-theme="red"] { --theme-primary: #dc2626; --theme-secondary: #f87171; --theme-dark: #450a0a; --theme-mid: #991b1b; --theme-soft-50: #fef2f2; --theme-soft-100: #fee2e2; --theme-soft-200: #fecaca; --theme-secondary-light: #fca5a5; --theme-secondary-lighter: #fecaca; }
        html[data-color-theme="slate"] { --theme-primary: #475569; --theme-secondary: #94a3b8; --theme-dark: #0f172a; --theme-mid: #334155; --theme-soft-50: #f8fafc; --theme-soft-100: #f1f5f9; --theme-soft-200: #e2e8f0; --theme-secondary-light: #cbd5e1; --theme-secondary-lighter: #e2e8f0; }
        html[data-color-theme="cyan"] { --theme-primary: #0891b2; --theme-secondary: #22d3ee; --theme-dark: #083344; --theme-mid: #0e7490; --theme-soft-50: #ecfeff; --theme-soft-100: #cffafe; --theme-soft-200: #a5f3fc; --theme-secondary-light: #67e8f9; --theme-secondary-lighter: #a5f3fc; }

        html[data-color-theme] [class~="bg-[#01553d]"] { background-color: var(--theme-primary) !important; }
        html[data-color-theme] [class~="text-[#01553d]"],
        html[data-color-theme] [class~="hover:text-[#01553d]"]:hover { color: var(--theme-primary) !important; }
        html[data-color-theme] [class~="bg-[#0a2a1d]"],
        html[data-color-theme] [class~="hover:bg-[#0a2a1d]"]:hover { background-color: var(--theme-dark) !important; }
        html[data-color-theme] [class~="text-[#0a2a1d]"] { color: var(--theme-dark) !important; }
        html[data-color-theme] [class~="bg-[#144832]"] { background-color: var(--theme-mid) !important; }
        html[data-color-theme] [class~="bg-[#79c143]"] { background-color: var(--theme-secondary) !important; }
        html[data-color-theme] [class~="text-[#a6e06f]"] { color: var(--theme-secondary-light) !important; }
        html[data-color-theme] [class~="text-[#b8ed8c]"] { color: var(--theme-secondary-lighter) !important; }
        html[data-color-theme] [class~="focus:border-[#2b805b]"]:focus { border-color: var(--theme-primary) !important; }
        html[data-color-theme] [data-color-theme-choice][aria-pressed="true"] { border-color: var(--theme-primary); box-shadow: 0 0 0 2px color-mix(in srgb, var(--theme-primary) 15%, transparent); }

        html[data-color-theme] .theme-primary-text { color: var(--theme-primary) !important; }
        html[data-color-theme] .theme-gradient-hero { background-image: linear-gradient(to right, var(--theme-primary), var(--theme-mid), var(--theme-dark)) !important; }
        html[data-color-theme] .theme-gradient-featured { background-image: linear-gradient(to bottom right, var(--theme-primary), var(--theme-mid), var(--theme-dark)) !important; }
        html[data-color-theme] .theme-gradient-accent { background-image: linear-gradient(to bottom right, var(--theme-primary), var(--theme-secondary)) !important; }
        html[data-color-theme] .theme-gradient-chart { background-image: linear-gradient(to top, var(--theme-primary), var(--theme-secondary)) !important; }
        html[data-color-theme] .theme-gradient-horizontal { background-image: linear-gradient(to right, var(--theme-primary), var(--theme-secondary)) !important; }
        html[data-color-theme] #dashboard-content .group:hover .theme-gradient-chart-hover { background-image: linear-gradient(to top, var(--theme-primary), var(--theme-secondary)) !important; }

        html[data-color-theme] #dashboard-content [class~="from-[#01553d]"] { --tw-gradient-from: var(--theme-primary) var(--tw-gradient-from-position) !important; }
        html[data-color-theme] #dashboard-content [class~="via-[#014532]"],
        html[data-color-theme] #dashboard-content [class~="via-[#046a4c]"] { --tw-gradient-via: var(--theme-mid) var(--tw-gradient-via-position) !important; }
        html[data-color-theme] #dashboard-content [class~="to-[#0a2a1d]"] { --tw-gradient-to: var(--theme-dark) var(--tw-gradient-to-position) !important; }
        html[data-color-theme] #dashboard-content [class~="from-[#79c143]"] { --tw-gradient-from: var(--theme-secondary) var(--tw-gradient-from-position) !important; }
        html[data-color-theme] #dashboard-content [class~="to-[#79c143]"] { --tw-gradient-to: var(--theme-secondary) var(--tw-gradient-to-position) !important; }
        html[data-color-theme] #dashboard-content [class~="bg-emerald-50"],
        html[data-color-theme] #dashboard-content [class~="from-emerald-50"] { background-color: var(--theme-soft-50) !important; --tw-gradient-from: var(--theme-soft-50) var(--tw-gradient-from-position) !important; }
        html[data-color-theme] #dashboard-content [class~="bg-emerald-100"],
        html[data-color-theme] #dashboard-content [class~="to-emerald-100"] { background-color: var(--theme-soft-100) !important; --tw-gradient-to: var(--theme-soft-100) var(--tw-gradient-to-position) !important; }
        html[data-color-theme] #dashboard-content [class~="from-emerald-100"] { --tw-gradient-from: var(--theme-soft-100) var(--tw-gradient-from-position) !important; }
        html[data-color-theme] #dashboard-content [class~="to-emerald-200"] { --tw-gradient-to: var(--theme-soft-200) var(--tw-gradient-to-position) !important; }
        html[data-color-theme] #dashboard-content [class~="hover:bg-emerald-50/40"]:hover { background-color: color-mix(in srgb, var(--theme-soft-50) 40%, transparent) !important; }

        @media (max-width: 640px) {
            .responsive-card-table {
                display: block;
                width: 100%;
                min-width: 0 !important;
            }

            .responsive-card-table thead {
                display: none;
            }

            .responsive-card-table tbody {
                display: block;
                width: 100%;
            }

            .responsive-card-table tbody tr:not(.responsive-table-empty) {
                display: block;
                margin: 0 0 0.75rem;
                padding: 0 0.875rem;
                border: 1px solid #e2e8f0;
                border-bottom: 1px solid #e2e8f0 !important;
                border-radius: 1rem;
                background: white;
            }

            .responsive-card-table tbody td:not([colspan]) {
                display: flex;
                width: 100%;
                max-width: none;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.75rem 0;
                border-bottom: 1px solid #f1f5f9;
                text-align: right;
                overflow-wrap: anywhere;
            }

            .responsive-card-table tbody td[data-label="Machine"] {
                overflow: visible;
                text-overflow: clip;
                white-space: normal;
            }

            .responsive-card-table tbody td:not([colspan])::before {
                flex: 0 0 auto;
                color: #94a3b8;
                content: attr(data-label);
                font-size: 0.65rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                text-align: left;
                text-transform: uppercase;
            }

            .responsive-card-table tbody td:last-child {
                border-bottom: 0;
            }

            .responsive-card-table tbody tr.responsive-table-empty {
                display: block;
                border: 0;
            }

            .responsive-card-table tbody tr.responsive-table-empty td {
                display: block;
                padding: 2rem 0.5rem;
                text-align: center;
            }
        }
    </style>
</head>
<body class="min-h-full text-slate-800 antialiased">
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-950/40 lg:hidden"></div>
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-[#0a2a1d] text-white transition-transform duration-200 lg:translate-x-0">
        <a href="{{ route('dashboard') }}" class="flex h-[76px] items-center gap-3 border-b border-white/10 px-6">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#79c143] text-2xl font-black text-[#0a2a1d]">K</span>
            <span class="min-w-0"><span class="block whitespace-nowrap text-lg font-bold tracking-tight">Kikimo <span class="text-[#79c143]">Dev</span></span><span class="block whitespace-nowrap text-[10px] font-medium tracking-[0.22em] text-[#a6e06f]">DESKTOP POS</span></span>
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
            <a href="{{ route('account.password.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('account.password.*') ? 'bg-[#144832] text-white' : 'text-emerald-50/70 hover:bg-white/10 hover:text-white' }}">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg text-emerald-100/60"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3m-4 5v2"/></svg></span>
                Change password
            </a>
            <a href="{{ route('account.appearance') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('account.appearance') ? 'bg-[#144832] text-white' : 'text-emerald-50/70 hover:bg-white/10 hover:text-white' }}">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg text-emerald-100/60"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 3v18m0-9h9"/></svg></span>
                Appearance
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
    <script>
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const appShell = document.getElementById('app-shell');
        const desktopNavigation = window.matchMedia('(min-width: 1024px)');

        const closeSidebar = () => {
            sidebar?.classList.add('-translate-x-full');
            backdrop?.classList.add('hidden');
            sidebarToggle?.setAttribute('aria-expanded', 'false');
        };

        sidebarToggle?.setAttribute(
            'aria-expanded',
            String(desktopNavigation.matches && !sidebar?.classList.contains('lg:-translate-x-full')),
        );

        sidebarToggle?.addEventListener('click', () => {
            if (desktopNavigation.matches) {
                const collapsed = sidebar?.classList.contains('lg:-translate-x-full') ?? false;
                sidebar?.classList.toggle('lg:-translate-x-full', !collapsed);
                sidebar?.classList.toggle('lg:translate-x-0', collapsed);
                appShell?.classList.toggle('lg:pl-0', !collapsed);
                appShell?.classList.toggle('lg:pl-64', collapsed);
                sidebarToggle.setAttribute('aria-expanded', String(collapsed));
                return;
            }

            const opening = sidebar?.classList.contains('-translate-x-full') ?? false;
            sidebar?.classList.toggle('-translate-x-full', !opening);
            backdrop?.classList.toggle('hidden', !opening);
            sidebarToggle.setAttribute('aria-expanded', String(opening));
        });

        backdrop?.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !desktopNavigation.matches) {
                closeSidebar();
            }
        });
        desktopNavigation.addEventListener('change', (event) => {
            sidebarToggle?.setAttribute(
                'aria-expanded',
                String(event.matches && !sidebar?.classList.contains('lg:-translate-x-full')),
            );
            if (event.matches) {
                backdrop?.classList.add('hidden');
            }
        });

        document.querySelectorAll('[data-color-theme-choice]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.colorThemeChoice === document.documentElement.dataset.colorTheme));
            button.addEventListener('click', () => {
                const theme = button.dataset.colorThemeChoice;
                if (!window.kikimoColorThemes.includes(theme)) {
                    return;
                }

                localStorage.setItem('kikimo-color-theme', theme);
                document.documentElement.dataset.colorTheme = theme;
                document.querySelectorAll('[data-color-theme-choice]').forEach((choice) => {
                    choice.setAttribute('aria-pressed', String(choice === button));
                });
                const status = document.getElementById('color-theme-status');
                if (status) {
                    status.textContent = `Theme changed to ${button.dataset.colorThemeLabel}.`;
                }
            });
        });
    </script>
</body>
</html>
