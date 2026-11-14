@extends('layouts.app')

@section('title', 'Synchronization history')

@section('content')
    <div class="mb-6">
        <p class="text-sm font-medium text-[#01553d]">Desktop connection</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">Synchronization history</h1>
        <p class="mt-1 text-sm text-slate-500">Original desktop payloads and their processing status.</p>
    </div>

    <form method="GET" class="mb-5 grid gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
        <select name="status" class="w-full min-w-0 rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-600">
            <option value="">All statuses</option>
            @foreach (['pending', 'processing', 'success', 'failed'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <input name="entity_type" value="{{ request('entity_type') }}" placeholder="Entity type" class="w-full min-w-0 rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        <input type="date" name="from" value="{{ request('from') }}" aria-label="From date" class="w-full min-w-0 rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        <input type="date" name="to" value="{{ request('to') }}" aria-label="To date" class="w-full min-w-0 rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        <div class="flex gap-2">
            <button class="min-h-11 flex-1 rounded-xl bg-[#01553d] px-4 py-2.5 text-sm font-semibold text-white">Filter</button>
            <a href="{{ route('sync.index') }}" class="inline-flex min-h-11 items-center justify-center whitespace-nowrap rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-600">Clear</a>
        </div>
    </form>

    <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-4 text-sm text-slate-500 sm:px-5">Sync records</div>
        <div class="overflow-x-auto">
            <table class="responsive-card-table w-full min-w-[950px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-400">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Sync ID</th>
                        <th class="px-5 py-3 font-semibold">Machine</th>
                        <th class="px-5 py-3 font-semibold">Entity</th>
                        <th class="px-5 py-3 font-semibold">Operation</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Received</th>
                        <th class="px-5 py-3 font-semibold">Processed</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($syncLogs as $syncLog)
                        <tr class="hover:bg-slate-50/70">
                            <td data-label="Sync ID" class="px-5 py-4 font-mono text-xs text-slate-500">#{{ $syncLog->id }}</td>
                            <td data-label="Machine" class="max-w-40 truncate px-5 py-4 font-mono text-xs text-slate-600" title="{{ $syncLog->machine_id }}">{{ $syncLog->machine_id }}</td>
                            <td data-label="Entity" class="px-5 py-4">
                                <span class="font-medium text-slate-700">{{ ucfirst($syncLog->entity_type) }}</span>
                                <span class="block text-xs text-slate-400">ID {{ $syncLog->entity_id }}</span>
                            </td>
                            <td data-label="Operation" class="px-5 py-4 capitalize text-slate-600">{{ $syncLog->operation }}</td>
                            <td data-label="Status" class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ match ($syncLog->status) {'success' => 'bg-emerald-50 text-emerald-700', 'failed' => 'bg-rose-50 text-rose-700', 'processing' => 'bg-amber-50 text-amber-700', default => 'bg-slate-100 text-slate-600'} }}">
                                    {{ ucfirst($syncLog->status) }}
                                </span>
                            </td>
                            <td data-label="Received" class="px-5 py-4 text-slate-500">{{ $syncLog->received_at?->setTimezone(config('app.business_timezone'))->format('M j, Y H:i:s') ?: '—' }}</td>
                            <td data-label="Processed" class="px-5 py-4 text-slate-500">{{ $syncLog->processed_at?->setTimezone(config('app.business_timezone'))->format('M j, Y H:i:s') ?: '—' }}</td>
                            <td data-label="Action" class="px-5 py-4 text-right">
                                <a href="{{ route('sync.show', $syncLog) }}" class="font-semibold text-[#01553d] hover:underline">Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="responsive-table-empty">
                            <td colspan="8" class="px-5 py-16 text-center text-sm text-slate-400">No synchronization records found for this business.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="max-w-full overflow-x-auto border-t border-slate-100 px-4 py-4 sm:px-5">
            {{ $syncLogs->links() }}
        </div>
    </section>
@endsection
