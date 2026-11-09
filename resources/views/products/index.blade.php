@extends('layouts.app')
@section('title', 'Products')
@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-[#01553d]">Catalog</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Products</h1>
            <p class="mt-1 text-sm text-slate-500">Products received in synchronized desktop payloads.</p>
        </div>
        <a href="{{ route('sync.index', ['entity_type' => 'product']) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Product sync history</a>
    </div>
    <form method="GET" class="mb-5 flex max-w-xl gap-2 rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
        <input name="q" value="{{ request('q') }}" placeholder="Search name, SKU or barcode" class="min-w-0 flex-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-[#2b805b] focus:outline-none">
        <button class="min-h-11 rounded-xl bg-[#01553d] px-4 py-2.5 text-sm font-semibold text-white">Search</button>
    </form>
    <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-4 text-sm text-slate-500 sm:px-5"><span class="font-semibold text-slate-800">{{ $products->total() }}</span> products</div>
        <div class="overflow-x-auto">
            <table class="responsive-card-table w-full min-w-[760px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-400">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Product</th>
                        <th class="px-5 py-3 font-semibold">Desktop ID</th>
                        <th class="px-5 py-3 font-semibold">SKU / barcode</th>
                        <th class="px-5 py-3 text-right font-semibold">Price</th>
                        <th class="px-5 py-3 text-right font-semibold">Stock</th>
                        <th class="px-5 py-3 font-semibold">Last synced</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/70">
                            <td data-label="Product" class="px-5 py-4 font-medium text-slate-800"><a href="{{ route('products.show', $product) }}" class="hover:text-[#01553d] hover:underline">{{ $product->name }}</a><span class="mt-0.5 block text-xs font-normal text-slate-400">{{ $product->terminal?->terminal_name ?: 'Terminal unavailable' }} · <a href="{{ route('products.show', $product) }}" class="text-[#01553d] hover:underline">View details</a></span></td>
                            <td data-label="Desktop ID" class="px-5 py-4 text-slate-500">{{ $product->desktop_product_id }}</td>
                            <td data-label="SKU / barcode" class="px-5 py-4 text-slate-600">{{ $product->sku ?: '—' }}<span class="mt-0.5 block text-xs text-slate-400">{{ $product->barcode ?: '' }}</span></td>
                            <td data-label="Price" class="px-5 py-4 text-right font-medium">{{ $product->unit_price === null ? '—' : auth()->user()->business->currency.' '.number_format((float) $product->unit_price, 2) }}</td>
                            <td data-label="Stock" class="px-5 py-4 text-right text-slate-600">{{ $product->stock_quantity === null ? '—' : number_format((float) $product->stock_quantity, 3) }}</td>
                            <td data-label="Last synced" class="px-5 py-4 text-slate-500">{{ $product->synced_at?->diffForHumans() ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr class="responsive-table-empty"><td colspan="6" class="px-5 py-16 text-center"><p class="font-medium text-slate-700">No synchronized products yet</p><p class="mt-1 text-sm text-slate-400">Products appear when your desktop POS sends product sync payloads.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="max-w-full overflow-x-auto border-t border-slate-100 px-4 py-4 sm:px-5">{{ $products->links() }}</div>
    </section>
@endsection
