@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-medium text-[#01553d]">{{ $todayLabel }}</p><h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">Business overview</h1><p class="mt-1 text-sm text-slate-500">Here’s how your store is performing today.</p></div>
        <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#01553d] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0a2a1d]">View all sales <span aria-hidden="true">→</span></a>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([['Today’s sales', $todaySales, 'Gross sales recorded today', true], ['Transactions', $todayTransactions, 'Completed sales today', false], ['Amount received', $todayReceived, 'Payments recorded today', true], ['Change given', $todayChange, 'Change returned today', true]] as [$label, $value, $hint, $isMoney])
            <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm shadow-slate-900/[0.03]">
                <div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-500">{{ $label }}</p><span class="h-2.5 w-2.5 rounded-full bg-[#79c143]"></span></div>
                <p class="mt-4 text-2xl font-semibold tracking-tight text-slate-900">{{ $isMoney ? auth()->user()->business->currency.' '.number_format((float) $value, 2) : number_format((float) $value) }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
            </article>
        @endforeach
    </div>
    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm xl:col-span-2 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="font-semibold text-slate-900">Sales activity</h2><p class="mt-1 text-sm text-slate-500">Daily sales for the last seven days</p></div><div class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-[#01553d]">{{ auth()->user()->business->currency }} {{ number_format((float) $monthlySales, 2) }} <span class="font-normal text-emerald-800/70">this month</span></div></div>
            <div class="mt-8 flex h-48 items-end gap-3 border-b border-slate-100 px-2">
                @foreach($daily as $day)
                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-2"><div class="w-full max-w-12 rounded-t-lg bg-gradient-to-t from-[#01553d] to-[#79c143] transition hover:opacity-80" style="height: {{ $day['total'] > 0 ? max(5, ($day['total'] / $dailyMaximum) * 100) : 3 }}%" title="{{ auth()->user()->business->currency }} {{ number_format($day['total'], 2) }}"></div><span class="pb-2 text-[11px] font-medium text-slate-400">{{ $day['label'] }}</span></div>
                @endforeach
            </div>
        </section>
        <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <div><h2 class="font-semibold text-slate-900">Payment methods</h2><p class="mt-1 text-sm text-slate-500">Today’s sales breakdown</p></div>
            @forelse($paymentMethods as $method)
                <div class="mt-5"><div class="mb-2 flex justify-between gap-3 text-sm"><span class="truncate font-medium text-slate-700">{{ $method->method }}</span><span class="shrink-0 font-semibold text-slate-900">{{ auth()->user()->business->currency }} {{ number_format((float) $method->amount, 2) }}</span></div><div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-[#79c143]" style="width: {{ $todaySales > 0 ? min(100, (float) $method->amount / $todaySales * 100) : 0 }}%"></div></div></div>
            @empty <p class="mt-10 text-center text-sm text-slate-400">No payment data yet today.</p>
            @endforelse
        </section>
    </div>
    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between px-5 py-5 sm:px-6"><div><h2 class="font-semibold text-slate-900">Recent transactions</h2><p class="mt-1 text-sm text-slate-500">Latest sales synced from your terminals</p></div><a href="{{ route('sales.index') }}" class="text-sm font-semibold text-[#01553d] hover:underline">All sales</a></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[650px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-400"><tr><th class="px-6 py-3 font-semibold">Receipt</th><th class="px-6 py-3 font-semibold">Cashier</th><th class="px-6 py-3 font-semibold">Payment</th><th class="px-6 py-3 text-right font-semibold">Total</th><th class="px-6 py-3 font-semibold">Time</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($recentSales as $sale)<tr class="hover:bg-slate-50/70"><td class="px-6 py-3.5"><a href="{{ route('sales.show', $sale) }}" class="font-semibold text-[#01553d] hover:underline">{{ $sale->receipt_no ?: '#'.$sale->desktop_sale_id }}</a></td><td class="px-6 py-3.5 text-slate-600">{{ $sale->cashier_name ?: '—' }}</td><td class="px-6 py-3.5 text-slate-600">{{ $sale->payment_method ?: '—' }}</td><td class="px-6 py-3.5 text-right font-semibold">{{ auth()->user()->business->currency }} {{ number_format((float) $sale->total, 2) }}</td><td class="px-6 py-3.5 text-slate-500">{{ $sale->sold_at?->format('H:i') ?: '—' }}</td></tr>@empty<tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">Your synced sales will appear here.</td></tr>@endforelse</tbody></table></div>
        </section>
        <section class="space-y-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><div><p class="text-sm font-medium text-slate-500">Active terminals</p><p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($activeTerminals) }}</p></div><span class="rounded-xl bg-emerald-50 p-3 text-[#01553d]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="3" width="16" height="14" rx="2"/><path d="M8 21h8m-4-4v4"/></svg></span></div><a href="{{ route('terminals.index') }}" class="mt-4 inline-block text-sm font-semibold text-[#01553d] hover:underline">Manage terminals →</a></div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><div><p class="text-sm font-medium text-slate-500">Sync attention</p><p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($syncPending + $syncFailed) }}</p></div><span class="rounded-xl {{ $syncFailed ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-[#01553d]' }} p-3"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9a7 7 0 0 1 11.6-2L20 12M4 12l2.8 5a7 7 0 0 0 11.6-2"/></svg></span></div><p class="mt-3 text-xs text-slate-400">{{ $syncPending }} waiting · {{ $syncFailed }} failed</p><a href="{{ route('sync.index') }}" class="mt-2 inline-block text-sm font-semibold text-[#01553d] hover:underline">Review sync history →</a></div>
        </section>
    </div>
    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6"><h2 class="font-semibold text-slate-900">Top products today</h2><div class="mt-4 divide-y divide-slate-100">@forelse($topProducts as $product)<div class="flex items-center justify-between gap-3 py-3"><div class="min-w-0"><p class="truncate text-sm font-medium text-slate-700">{{ $product->product_name }}</p><p class="text-xs text-slate-400">{{ number_format((float) $product->units, 2) }} units</p></div><p class="shrink-0 text-sm font-semibold">{{ auth()->user()->business->currency }} {{ number_format((float) $product->amount, 2) }}</p></div>@empty<p class="py-7 text-center text-sm text-slate-400">Product performance appears after sales sync.</p>@endforelse</div></section>
        <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6"><h2 class="font-semibold text-slate-900">Top cashiers today</h2><div class="mt-4 divide-y divide-slate-100">@forelse($topCashiers as $cashier)<div class="flex items-center justify-between gap-3 py-3"><div class="min-w-0"><p class="truncate text-sm font-medium text-slate-700">{{ $cashier->cashier_name }}</p><p class="text-xs text-slate-400">{{ $cashier->transactions }} transactions</p></div><p class="shrink-0 text-sm font-semibold">{{ auth()->user()->business->currency }} {{ number_format((float) $cashier->amount, 2) }}</p></div>@empty<p class="py-7 text-center text-sm text-slate-400">Cashier performance appears after sales sync.</p>@endforelse</div></section>
    </div>
@endsection
