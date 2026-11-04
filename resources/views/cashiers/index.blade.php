@extends('layouts.app')
@section('title', 'Cashiers')
@section('content')
    <div class="mb-6">
        <p class="text-sm font-medium text-[#01553d]">Team performance</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Cashiers</h1>
        <p class="mt-1 text-sm text-slate-500">Performance derived from synchronized sales.</p>
    </div>
    <form method="GET" class="mb-5 flex max-w-lg gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
        <input name="q" value="{{ request('q') }}" placeholder="Search cashier name or ID" class="min-w-0 flex-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-[#2b805b] focus:outline-none">
        <button class="min-h-11 rounded-xl bg-[#01553d] px-4 py-2.5 text-sm font-semibold text-white">Search</button>
    </form>
    <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="responsive-card-table w-full min-w-[680px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-400">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Cashier</th>
                        <th class="px-5 py-3 font-semibold">Cashier ID</th>
                        <th class="px-5 py-3 text-right font-semibold">Transactions</th>
                        <th class="px-5 py-3 text-right font-semibold">Average sale</th>
                        <th class="px-5 py-3 text-right font-semibold">Total sales</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cashiers as $cashier)
                        <tr>
                            <td data-label="Cashier" class="px-5 py-4 font-medium text-slate-800">{{ $cashier->cashier_name }}</td>
                            <td data-label="Cashier ID" class="px-5 py-4 text-slate-500">{{ $cashier->cashier_id ?: '—' }}</td>
                            <td data-label="Transactions" class="px-5 py-4 text-right text-slate-600">{{ number_format($cashier->transactions) }}</td>
                            <td data-label="Average sale" class="px-5 py-4 text-right text-slate-600">{{ auth()->user()->business->currency }} {{ number_format((float) $cashier->average_sale, 2) }}</td>
                            <td data-label="Total sales" class="px-5 py-4 text-right font-semibold">{{ auth()->user()->business->currency }} {{ number_format((float) $cashier->total_sales, 2) }}</td>
                        </tr>
                    @empty
                        <tr class="responsive-table-empty"><td colspan="5" class="px-5 py-16 text-center text-sm text-slate-400">Cashier data will appear when synchronized sales include cashier details.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="max-w-full overflow-x-auto border-t border-slate-100 px-4 py-4 sm:px-5">{{ $cashiers->links() }}</div>
    </section>
@endsection
