@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
    $currency = auth()->user()->business->currency;
    $dailyMax = max((float) $dailyMaximum, 1);
    $lastIndex = count($daily) - 1;

    $stats = [
        [
            'label' => 'Today’s sales',
            'value' => $todaySales,
            'hint'  => 'Gross sales recorded today',
            'money' => true,
            'icon'  => 'M3 3v18h18M7 14l4-4 3 3 5-6',
            'tone'  => 'featured',
        ],
        [
            'label' => 'Transactions',
            'value' => $todayTransactions,
            'hint'  => 'Completed sales today',
            'money' => false,
            'icon'  => 'M9 5h10M9 12h10M9 19h10M4 5h.01M4 12h.01M4 19h.01',
            'tone'  => 'emerald',
        ],
        [
            'label' => 'Amount received',
            'value' => $todayReceived,
            'hint'  => 'Payments recorded today',
            'money' => true,
            'icon'  => 'M12 3v12m0 0l-4-4m4 4l4-4M5 21h14',
            'tone'  => 'sky',
        ],
        [
            'label' => 'Change given',
            'value' => $todayChange,
            'hint'  => 'Change returned today',
            'money' => true,
            'icon'  => 'M12 21V9m0 0l-4 4m4-4l4 4M5 3h14',
            'tone'  => 'amber',
        ],
    ];

    $tones = [
        'emerald' => ['tile' => 'bg-emerald-50 theme-primary-text ring-emerald-100', 'bar' => 'theme-gradient-horizontal'],
        'sky'     => ['tile' => 'bg-sky-50 text-sky-600 ring-sky-100',           'bar' => 'from-sky-500 to-sky-300'],
        'amber'   => ['tile' => 'bg-amber-50 text-amber-600 ring-amber-100',     'bar' => 'from-amber-500 to-amber-300'],
    ];
@endphp

<div id="dashboard-content">
{{-- Compact hero --}}
<div class="relative mb-5 overflow-hidden rounded-2xl theme-gradient-hero px-5 py-4 text-white shadow-md shadow-emerald-900/10">
    <div class="pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full bg-[#79c143]/20 blur-2xl"></div>
    <div class="relative flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="flex items-center gap-2 text-xs font-medium text-emerald-100/80">
                <span class="relative flex h-1.5 w-1.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#79c143] opacity-75"></span><span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-[#79c143]"></span></span>
                {{ $todayLabel }}
            </p>
            <h1 class="mt-0.5 text-lg font-semibold tracking-tight sm:text-xl">Business overview
                <span class="ml-2 hidden text-sm font-normal text-emerald-100/70 md:inline">Here’s how your store is performing today.</span>
            </h1>
        </div>
        <div class="flex items-center gap-2.5">
            <div class="rounded-xl bg-white/10 px-3 py-1.5 ring-1 ring-white/15 backdrop-blur">
                <p class="text-[10px] uppercase tracking-wider text-emerald-100/70">This month</p>
                <p class="text-sm font-semibold leading-tight">{{ $currency }} {{ number_format((float) $monthlySales, 2) }}</p>
            </div>
            <a href="{{ route('sales.index') }}" class="group inline-flex items-center gap-1.5 rounded-lg bg-[#79c143] px-3.5 py-2 text-sm font-semibold text-[#0a2a1d] transition hover:bg-white">
                View all sales
                <span aria-hidden="true" class="transition group-hover:translate-x-0.5">→</span>
            </a>
        </div>
    </div>
</div>

{{-- Stats cards --}}
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($stats as $stat)
        @if($stat['tone'] === 'featured')
            <article class="group relative overflow-hidden rounded-2xl theme-gradient-featured p-5 text-white shadow-lg shadow-emerald-900/15 transition duration-200 hover:-translate-y-0.5 hover:shadow-xl">
                <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-[#79c143]/25 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-10 -left-6 h-28 w-28 rounded-full bg-white/5"></div>

                <div class="relative flex items-center justify-between">
                    <p class="text-sm font-medium text-emerald-100/90">{{ $stat['label'] }}</p>
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-white/15 ring-1 ring-white/20 backdrop-blur">
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $stat['icon'] }}"/></svg>
                    </span>
                </div>

                <div class="relative mt-4 flex items-end justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-2xl font-semibold tracking-tight sm:text-[1.7rem]">
                            <span class="mr-1 text-sm font-medium text-emerald-100/70">{{ $currency }}</span>{{ number_format((float) $stat['value'], 2) }}
                        </p>
                        <p class="mt-1 text-xs text-emerald-100/70">{{ $stat['hint'] }}</p>
                    </div>
                    {{-- mini 7-day trend --}}
                    <div class="flex h-10 shrink-0 items-end gap-1" aria-hidden="true">
                        @foreach($daily as $i => $day)
                            @php $h = $day['total'] > 0 ? max(12, ($day['total'] / $dailyMax) * 100) : 8; @endphp
                            <span class="w-1.5 rounded-full {{ $i === $lastIndex ? 'bg-[#79c143]' : 'bg-white/30' }}" style="height: {{ $h }}%"></span>
                        @endforeach
                    </div>
                </div>
            </article>
        @else
            @php $t = $tones[$stat['tone']]; @endphp
            <article class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-5 shadow-sm shadow-slate-900/[0.03] transition duration-200 hover:-translate-y-0.5 hover:border-slate-200 hover:shadow-lg hover:shadow-slate-900/5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                    <span class="grid h-9 w-9 place-items-center rounded-xl ring-1 transition group-hover:scale-105 {{ $t['tile'] }}">
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $stat['icon'] }}"/></svg>
                    </span>
                </div>

                <p class="mt-4 truncate text-2xl font-semibold tracking-tight text-slate-900 sm:text-[1.7rem]">
                    @if($stat['money'])
                        <span class="mr-1 text-sm font-medium text-slate-400">{{ $currency }}</span>{{ number_format((float) $stat['value'], 2) }}
                    @else
                        {{ number_format((float) $stat['value']) }}
                    @endif
                </p>
                <p class="mt-1 text-xs text-slate-400">{{ $stat['hint'] }}</p>

                {{-- accent underline that grows on hover --}}
                <span class="absolute inset-x-0 bottom-0 h-1 origin-left scale-x-[0.18] bg-gradient-to-r transition-transform duration-300 group-hover:scale-x-100 {{ $t['bar'] }}"></span>
            </article>
        @endif
    @endforeach
</div>

{{-- Sales activity + payment methods --}}
<div class="mt-4 grid gap-4 xl:grid-cols-3">
    <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6 xl:col-span-2">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="font-semibold text-slate-900">Sales activity</h2>
                <p class="mt-1 text-sm text-slate-500">Daily sales for the last seven days</p>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold theme-primary-text ring-1 ring-emerald-100">
                <span class="h-1.5 w-1.5 rounded-full bg-[#79c143]"></span>
                {{ $currency }} {{ number_format((float) $monthlySales, 2) }} <span class="font-normal text-emerald-800/70">this month</span>
            </span>
        </div>

        <div class="relative mt-8">
            <div class="pointer-events-none absolute inset-x-0 top-0 flex h-48 flex-col justify-between">
                @foreach(range(0, 3) as $i)
                    <div class="border-t border-dashed border-slate-100"></div>
                @endforeach
            </div>
            <div class="relative flex h-48 items-end gap-2 border-b border-slate-200 px-1 sm:gap-4">
                @foreach($daily as $i => $day)
                    @php
                        $isLast = $i === $lastIndex;
                        $height = $day['total'] > 0 ? max(5, ($day['total'] / $dailyMax) * 100) : 3;
                    @endphp
                    <div class="group relative flex h-full flex-1 flex-col items-center justify-end">
                        <div class="pointer-events-none absolute -top-1 z-10 -translate-y-full whitespace-nowrap rounded-lg bg-slate-900 px-2.5 py-1 text-[11px] font-semibold text-white opacity-0 shadow-lg transition group-hover:opacity-100">
                            {{ $currency }} {{ number_format($day['total'], 2) }}
                        </div>
                        <div class="w-full max-w-12 rounded-t-xl transition-all duration-300 group-hover:opacity-90 {{ $isLast ? 'theme-gradient-chart shadow-md shadow-emerald-900/10' : 'bg-gradient-to-t from-emerald-100 to-emerald-200 theme-gradient-chart-hover' }}" style="height: {{ $height }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex gap-2 px-1 sm:gap-4">
                @foreach($daily as $i => $day)
                    <span class="flex-1 text-center text-[11px] font-medium {{ $i === $lastIndex ? 'theme-primary-text' : 'text-slate-400' }}">{{ $day['label'] }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
        <div>
            <h2 class="font-semibold text-slate-900">Payment methods</h2>
            <p class="mt-1 text-sm text-slate-500">Today’s sales breakdown</p>
        </div>
        @forelse($paymentMethods as $method)
            @php $pct = $todaySales > 0 ? min(100, (float) $method->amount / $todaySales * 100) : 0; @endphp
            <div class="mt-5">
                <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                    <span class="flex min-w-0 items-center gap-2">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-[#79c143]"></span>
                        <span class="truncate font-medium text-slate-700">{{ $method->method }}</span>
                        <span class="shrink-0 rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500">{{ number_format($pct, 0) }}%</span>
                    </span>
                    <span class="shrink-0 font-semibold text-slate-900">{{ $currency }} {{ number_format((float) $method->amount, 2) }}</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-2 rounded-full theme-gradient-horizontal transition-all duration-500" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        @empty
            <div class="mt-8 flex flex-col items-center gap-2 py-6 text-center">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-50 text-slate-300">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                </span>
                <p class="text-sm text-slate-400">No payment data yet today.</p>
            </div>
        @endforelse
    </section>
</div>

{{-- Transactions + side cards --}}
<div class="mt-4 grid gap-4 xl:grid-cols-3">
    <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm xl:col-span-2">
        <div class="flex items-center justify-between px-5 py-5 sm:px-6">
            <div>
                <h2 class="font-semibold text-slate-900">Recent transactions</h2>
                <p class="mt-1 text-sm text-slate-500">Latest sales synced from your terminals</p>
            </div>
            <a href="{{ route('sales.index') }}" class="rounded-lg bg-emerald-50 px-3 py-1.5 text-sm font-semibold theme-primary-text transition hover:bg-emerald-100">All sales</a>
        </div>
        <div class="overflow-x-auto">
            <table class="responsive-card-table w-full min-w-[650px] text-left text-sm">
                <thead class="border-y border-slate-100 bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Receipt</th>
                        <th class="px-6 py-3 font-semibold">Cashier</th>
                        <th class="px-6 py-3 font-semibold">Payment</th>
                        <th class="px-6 py-3 text-right font-semibold">Total</th>
                        <th class="px-6 py-3 font-semibold">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentSales as $sale)
                        <tr class="transition hover:bg-emerald-50/40">
                            <td data-label="Receipt" class="px-6 py-3.5">
                                <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center gap-2 font-semibold theme-primary-text hover:underline">
                                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-50 theme-primary-text">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2zM9 7h6M9 11h6"/></svg>
                                    </span>
                                    {{ $sale->receipt_no ?: '#'.$sale->desktop_sale_id }}
                                </a>
                            </td>
                            <td data-label="Cashier" class="px-6 py-3.5">
                                @if($sale->cashier_name)
                                    <span class="inline-flex items-center gap-2 text-slate-600">
                                        <span class="grid h-7 w-7 place-items-center rounded-full theme-gradient-accent text-[11px] font-semibold text-white">{{ strtoupper(mb_substr($sale->cashier_name, 0, 1)) }}</span>
                                        {{ $sale->cashier_name }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td data-label="Payment" class="px-6 py-3.5">
                                @if($sale->payment_method)
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">{{ $sale->payment_method }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td data-label="Total" class="px-6 py-3.5 text-right font-semibold text-slate-900">{{ $currency }} {{ number_format((float) $sale->total, 2) }}</td>
                            <td data-label="Time" class="px-6 py-3.5 text-slate-500">{{ $sale->sold_at?->setTimezone(config('app.business_timezone'))->format('H:i') ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr class="responsive-table-empty">
                            <td colspan="5" class="px-6 py-14 text-center">
                                <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-50 text-slate-300">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2zM9 7h6M9 11h6"/></svg>
                                </span>
                                <p class="mt-3 text-sm text-slate-400">Your synced sales will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="space-y-4">
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Active terminals</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($activeTerminals) }}</p>
                </div>
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 theme-primary-text">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="3" width="16" height="14" rx="2"/><path d="M8 21h8m-4-4v4"/></svg>
                </span>
            </div>
            <a href="{{ route('terminals.index') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold theme-primary-text hover:underline">Manage terminals <span aria-hidden="true">→</span></a>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-medium text-slate-500">Sync attention</p>
                        @if($syncFailed)
                            <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-600 ring-1 ring-rose-100">Needs review</span>
                        @else
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold theme-primary-text ring-1 ring-emerald-100">Healthy</span>
                        @endif
                    </div>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($syncPending + $syncFailed) }}</p>
                </div>
                <span class="grid h-12 w-12 place-items-center rounded-2xl {{ $syncFailed ? 'bg-rose-50 text-rose-600' : 'bg-gradient-to-br from-emerald-50 to-emerald-100 theme-primary-text' }}">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9a7 7 0 0 1 11.6-2L20 12M4 12l2.8 5a7 7 0 0 0 11.6-2"/></svg>
                </span>
            </div>
            <div class="mt-3 flex gap-2 text-xs">
                <span class="rounded-md bg-amber-50 px-2 py-1 font-medium text-amber-700">{{ $syncPending }} waiting</span>
                <span class="rounded-md {{ $syncFailed ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-500' }} px-2 py-1 font-medium">{{ $syncFailed }} failed</span>
            </div>
            <a href="{{ route('sync.index') }}" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold theme-primary-text hover:underline">Review sync history <span aria-hidden="true">→</span></a>
        </div>
    </section>
</div>

{{-- Top products / cashiers --}}
<div class="mt-4 grid gap-4 lg:grid-cols-2">
    <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-slate-900">Top products today</h2>
            <span class="rounded-full bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-400">By revenue</span>
        </div>
        <div class="mt-4 space-y-1">
            @forelse($topProducts as $i => $product)
                <div class="flex items-center gap-3 rounded-xl p-2.5 transition hover:bg-slate-50">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl text-sm font-bold {{ $i === 0 ? 'theme-gradient-accent text-white' : 'bg-slate-100 text-slate-500' }}">{{ $i + 1 }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-700">{{ $product->product_name }}</p>
                        <p class="text-xs text-slate-400">{{ number_format((float) $product->units, 2) }} units</p>
                    </div>
                    <p class="shrink-0 text-sm font-semibold text-slate-900">{{ $currency }} {{ number_format((float) $product->amount, 2) }}</p>
                </div>
            @empty
                <div class="flex flex-col items-center gap-2 py-8 text-center">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-50 text-slate-300">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 8l-9-5-9 5v8l9 5 9-5V8zM3 8l9 5 9-5M12 13v8"/></svg>
                    </span>
                    <p class="text-sm text-slate-400">Product performance appears after sales sync.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-slate-900">Top cashiers today</h2>
            <span class="rounded-full bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-400">By revenue</span>
        </div>
        <div class="mt-4 space-y-1">
            @forelse($topCashiers as $i => $cashier)
                <div class="flex items-center gap-3 rounded-xl p-2.5 transition hover:bg-slate-50">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-sm font-semibold {{ $i === 0 ? 'theme-gradient-accent text-white' : 'bg-emerald-50 theme-primary-text' }}">{{ strtoupper(mb_substr($cashier->cashier_name, 0, 1)) }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-700">{{ $cashier->cashier_name }}</p>
                        <p class="text-xs text-slate-400">{{ $cashier->transactions }} transactions</p>
                    </div>
                    <p class="shrink-0 text-sm font-semibold text-slate-900">{{ $currency }} {{ number_format((float) $cashier->amount, 2) }}</p>
                </div>
            @empty
                <div class="flex flex-col items-center gap-2 py-8 text-center">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-50 text-slate-300">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                    </span>
                    <p class="text-sm text-slate-400">Cashier performance appears after sales sync.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection