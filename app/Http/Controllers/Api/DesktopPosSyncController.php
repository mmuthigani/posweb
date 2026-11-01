<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesktopPosSyncLog;
use App\Models\PosTerminal;
use App\Services\SyncProcessor;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DesktopPosSyncController extends Controller
{
    public function __invoke(Request $request, SyncProcessor $processor): JsonResponse
    {
        $request->merge(['idempotency_key' => $request->header('Idempotency-Key')]);

        $validated = $request->validate([
            'client_id' => ['required', 'string', 'max:100'],
            'machine_id' => ['required', 'string', 'max:100'],
            'entity_type' => ['required', 'string', 'max:50'],
            'entity_id' => ['required', 'integer', 'min:1'],
            'operation' => ['required', 'in:create,update,delete'],
            'payload' => ['required', 'array'],
            'idempotency_key' => ['required', 'string', 'max:150'],
            'payload.sale' => ['sometimes', 'array'],
            'payload.items' => ['sometimes', 'array'],
            'payload.items.*' => ['array'],
        ]);

        /** @var PosTerminal $terminal */
        $terminal = $request->attributes->get('pos_terminal');

        if ($validated['entity_type'] === 'sale' && $validated['operation'] !== 'delete') {
            $request->validate([
                'payload.sale.id' => ['required', 'integer', 'min:1'],
                'payload.items' => ['required', 'array'],
                'payload.items.*.product_name' => ['required', 'string', 'max:255'],
                'payload.items.*.quantity' => ['required', 'numeric', 'min:0'],
                'payload.items.*.unit_price' => ['required', 'numeric', 'min:0'],
                'payload.items.*.line_total' => ['required', 'numeric', 'min:0'],
            ]);
        }

        try {
            $syncLog = DesktopPosSyncLog::query()->create([
                'client_id' => $validated['client_id'],
                'machine_id' => $terminal->machine_id,
                'entity_type' => $validated['entity_type'],
                'entity_id' => $validated['entity_id'],
                'operation' => $validated['operation'],
                'payload' => $validated['payload'],
                'idempotency_key' => $validated['idempotency_key'],
                'status' => 'pending',
                'received_at' => now(),
            ]);
        } catch (QueryException $exception) {
            $existing = DesktopPosSyncLog::query()
                ->where('machine_id', $terminal->machine_id)
                ->where('idempotency_key', $validated['idempotency_key'])
                ->first();

            if (! $existing) {
                throw $exception;
            }

            return response()->json([
                'sync_id' => $existing->id,
                'status' => $existing->status,
                'duplicate' => true,
            ]);
        }

        $syncLog = $processor->process($syncLog);

        return response()->json([
            'sync_id' => $syncLog->id,
            'status' => $syncLog->status,
            'error' => $syncLog->status === 'failed' ? 'Import failed. The original payload has been retained for retry.' : null,
        ], $syncLog->status === 'failed' ? 422 : 200);
    }
}
