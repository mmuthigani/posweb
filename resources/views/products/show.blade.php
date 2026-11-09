@extends('layouts.app')
@section('title', $product->name)
@section('content')
    <div class="mb-6">
        <a href="{{ route('products.index') }}" class="text-sm font-medium text-[#01553d] hover:underline">← Products</a>
        <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-[#01553d]">Product details</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">{{ $product->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $product->terminal?->terminal_name ?: 'Terminal unavailable' }}</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">Desktop ID {{ $product->desktop_product_id }}</span>
        </div>
    </div>

    <section class="rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Received product fields</h2>
            <p class="mt-1 text-sm text-slate-500">Values are shown from the synchronized product payload; no product data has been changed.</p>
        </div>
        @if($product->payload)
            <dl class="grid gap-px bg-slate-100 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($product->payload as $key => $value)
                    @php
                        $displayValue = match ($key) {
                            'is_active' => in_array($value, [true, 1, '1'], true) ? 'Active' : 'Inactive',
                            'offer_enabled' => in_array($value, [true, 1, '1'], true) ? 'Enabled' : 'Disabled',
                            default => is_array($value) ? json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $value,
                        };
                    @endphp
                    <div class="min-w-0 bg-white px-5 py-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ \Illuminate\Support\Str::headline((string) $key) }}</dt>
                        <dd class="mt-1 break-words text-sm font-medium text-slate-800">{{ $displayValue === null || $displayValue === '' ? '—' : $displayValue }}</dd>
                    </div>
                @endforeach
            </dl>
        @else
            <p class="px-5 py-8 text-sm text-slate-500">No product payload details were received.</p>
        @endif
        <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-500">
            Last synced {{ $product->synced_at?->diffForHumans() ?: '—' }}
        </div>
    </section>
@endsection
