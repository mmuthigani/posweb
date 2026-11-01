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
    private const AUTHENTICATION_REQUIRED = '[awaiting-auth]';

    public function __invoke(Request $request, SyncProcessor $processor): JsonResponse
    {
        $request->merge(['idempotency_key' => $request->header('Idempotency-Key')]);

        $validated = $request->validate([
            'client_id' => ['required', 'string', 'max:100'],
            'machine_id' => ['required', 'string', 'max:100'],
            'entity_type' => ['required', 'string', 'in:sale,product'],
            'entity_id' => ['required', 'integer', 'min:1'],
            'operation' => ['required', 'in:create,update,delete'],
            'payload' => ['required', 'array'],
            'idempotency_key' => ['required', 'string', 'max:150'],
            'payload.sale' => ['sometimes', 'array'],
            'payload.items' => ['sometimes', 'array'],
            'payload.items.*' => ['array'],
        ]);

        /** @var PosTerminal|null $terminal */
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
                'machine_id' => $validated['machine_id'],
                'entity_type' => $validated['entity_type'],
                'entity_id' => $validated['entity_id'],
                'operation' => $validated['operation'],
                'payload' => $validated['payload'],
                'idempotency_key' => $validated['idempotency_key'],
                'status' => 'pending',
                'error_message' => $terminal ? null : self::AUTHENTICATION_REQUIRED.' Terminal registration and bearer authentication are required before import.',
                'received_at' => now(),
            ]);
        } catch (QueryException $exception) {
            $existing = DesktopPosSyncLog::query()
                ->where('machine_id', $validated['machine_id'])
                ->where('idempotency_key', $validated['idempotency_key'])
                ->first();

            if (! $existing) {
                throw $exception;
            }

            if ($terminal && $existing->status !== 'success' && $this->requestMatchesLog($validated, $existing)) {
                $syncLog = $processor->process($existing);
            } else {
                $syncLog = $existing;
            }

            return $this->duplicateResponse($syncLog);
        }

        if ($terminal) {
            $syncLog = $processor->process($syncLog);
        }

        return response()->json([
            'success' => true,
            'message' => 'Desktop POS sync request stored successfully.',
            'data' => [
                'sync_id' => $syncLog->id,
                'client_id' => $syncLog->client_id,
                'machine_id' => $syncLog->machine_id,
                'entity_type' => $syncLog->entity_type,
                'entity_id' => $syncLog->entity_id,
                'operation' => $syncLog->operation,
                'status' => $syncLog->status,
                'received_at' => $syncLog->received_at,
            ],
        ], 201);
    }

    private function duplicateResponse(DesktopPosSyncLog $syncLog): JsonResponse
    {
        return response()->json([
            'success' => $syncLog->status === 'success',
            'message' => 'This synchronization request has already been received.',
            'data' => [
                'sync_id' => $syncLog->id,
                'status' => $syncLog->status,
                'received_at' => $syncLog->received_at,
            ],
        ]);
    }

    private function requestMatchesLog(array $validated, DesktopPosSyncLog $syncLog): bool
    {
        return $syncLog->client_id === $validated['client_id']
            && $syncLog->machine_id === $validated['machine_id']
            && $syncLog->entity_type === $validated['entity_type']
            && (int) $syncLog->entity_id === (int) $validated['entity_id']
            && $syncLog->operation === $validated['operation']
            && $this->normalizePayload($syncLog->payload) === $this->normalizePayload($validated['payload']);
    }

    private function normalizePayload(array $payload): array
    {
        if (array_is_list($payload)) {
            return array_map(
                fn (mixed $value): mixed => is_array($value) ? $this->normalizePayload($value) : $value,
                $payload,
            );
        }

        ksort($payload);

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->normalizePayload($value);
            }
        }

        return $payload;
    }
}
