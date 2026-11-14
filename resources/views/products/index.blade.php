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
    <section>
        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="text-sm text-slate-500"><span class="font-semibold text-slate-800">{{ $products->total() }}</span> products</p>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Catalog · View only</p>
        </div>
        @if($products->count())
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 2xl:grid-cols-5">
                @foreach($products as $product)
                    <a href="{{ route('products.show', $product) }}" class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-slate-900/5 focus:outline-none focus:ring-4 focus:ring-emerald-900/10">
                        <div class="relative aspect-[4/3] overflow-hidden bg-slate-50">
                            @if($product->image_data_uri)
                                <img src="{{ $product->image_data_uri }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-50 to-slate-100">
                                    <svg class="h-12 w-12 text-emerald-900/20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><path d="M5 7.5A2.5 2.5 0 0 1 7.5 5h9A2.5 2.5 0 0 1 19 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 5 16.5v-9Z"/><circle cx="9" cy="9" r="1.5"/><path d="m19 15-4-4-8 8"/></svg>
                                </div>
                            @endif
                            <span class="absolute left-2 top-2 rounded-full bg-white/90 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-600 shadow-sm">ID {{ $product->desktop_product_id }}</span>
                            @if($product->stock_quantity !== null)
                                <span class="absolute bottom-2 right-2 rounded-full px-2.5 py-1 text-xs font-semibold shadow-sm {{ (float) $product->stock_quantity > 0 ? 'bg-emerald-700 text-white' : 'bg-slate-700 text-white' }}">
                                    {{ number_format((float) $product->stock_quantity, 3) }} in stock
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-3 sm:p-4">
                            <h2 class="line-clamp-2 min-h-10 text-sm font-semibold leading-5 text-slate-800 group-hover:text-[#01553d] sm:text-base">{{ $product->name }}</h2>
                            <p class="mt-1 truncate text-xs text-slate-400">{{ $product->sku ? 'SKU '.$product->sku : ($product->barcode ? 'Barcode '.$product->barcode : ($product->terminal?->terminal_name ?: 'Product')) }}</p>
                            <div class="mt-3 flex items-end justify-between gap-2 border-t border-slate-100 pt-3">
                                <span class="text-sm font-bold text-[#01553d] sm:text-base">{{ $product->unit_price === null ? 'Price unavailable' : auth()->user()->business->currency.' '.number_format((float) $product->unit_price, 2) }}</span>
                                <span class="shrink-0 text-xs font-semibold text-slate-400 group-hover:text-[#01553d]">Details <span aria-hidden="true">→</span></span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-16 text-center">
                <p class="font-medium text-slate-700">No synchronized products yet</p>
                <p class="mt-1 text-sm text-slate-400">Products appear when your desktop POS sends product sync payloads.</p>
            </div>
        @endif
        <div class="mt-5 max-w-full overflow-x-auto rounded-2xl border border-slate-100 bg-white px-4 py-4 sm:px-5">{{ $products->links() }}</div>
    </section>
@endsection
