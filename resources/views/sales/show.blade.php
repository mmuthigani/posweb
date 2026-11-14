@extends('layouts.app')
@section('title', 'Sale '.$sale->receipt_no)
@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('sales.index') }}" class="text-sm font-medium text-[#01553d] hover:underline">← Sales</a>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">{{ $sale->receipt_no ?: 'Sale #'.$sale->desktop_sale_id }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $sale->sold_at?->setTimezone(config('app.business_timezone'))->format('F j, Y · H:i') ?: 'Date unavailable' }}</p>
        </div>
        <a href="{{ route('sales.receipt', $sale) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Print receipt</a>
    </div>
    <div class="grid gap-5 lg:grid-cols-3">
        <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm lg:col-span-2 sm:p-6">
            <h2 class="font-semibold text-slate-900">Items sold</h2>
            <div class="mt-5 overflow-x-auto">
                <table class="responsive-card-table w-full min-w-[520px] text-left text-sm">
                    <thead class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                        <tr><th class="py-3 font-semibold">Product</th><th class="py-3 text-right font-semibold">Qty</th><th class="py-3 text-right font-semibold">Unit price</th><th class="py-3 text-right font-semibold">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($sale->items as $item)
                            <tr>
                                <td data-label="Product" class="py-3.5 font-medium text-slate-700">{{ $item->product_name }}@if($item->product_id)<span class="mt-0.5 block text-xs text-slate-400">Product ID {{ $item->product_id }}</span>@endif</td>
                                <td data-label="Qty" class="py-3.5 text-right text-slate-600">{{ number_format((float) $item->quantity, 3) }}</td>
                                <td data-label="Unit price" class="py-3.5 text-right text-slate-600">{{ $sale->business->currency }} {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td data-label="Total" class="py-3.5 text-right font-semibold">{{ $sale->business->currency }} {{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr class="responsive-table-empty"><td colspan="4" class="py-10 text-center text-slate-400">No item details were supplied for this sale.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <section class="space-y-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="font-semibold text-slate-900">Payment summary</h2>
                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Subtotal</dt><dd class="font-medium">{{ $sale->business->currency }} {{ number_format((float) $sale->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Total</dt><dd class="font-semibold">{{ $sale->business->currency }} {{ number_format((float) $sale->total, 2) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Amount received</dt><dd class="font-medium">{{ $sale->business->currency }} {{ number_format((float) $sale->amount_paid, 2) }}</dd></div>
                    <div class="flex justify-between gap-3 border-t border-slate-100 pt-3"><dt class="text-slate-500">Change given</dt><dd class="font-medium">{{ $sale->business->currency }} {{ number_format((float) $sale->change_due, 2) }}</dd></div>
                </dl>
                <div class="mt-5 rounded-xl bg-emerald-50 px-4 py-3"><p class="text-xs text-emerald-700">Payment method</p><p class="mt-1 font-semibold text-[#01553d]">{{ $sale->payment_method ?: 'Not specified' }}</p></div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-900">Transaction details</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Cashier</dt><dd class="text-right font-medium">{{ $sale->cashier_name ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Cashier ID</dt><dd class="font-medium">{{ $sale->cashier_id ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Terminal</dt><dd class="text-right font-medium">{{ $sale->terminal?->terminal_name ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Desktop sale ID</dt><dd class="font-medium">{{ $sale->desktop_sale_id }}</dd></div>
                </dl>
            </div>
        </section>
    </div>
@endsection
