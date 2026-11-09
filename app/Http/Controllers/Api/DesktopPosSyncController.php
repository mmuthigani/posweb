<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesktopPosSyncLog;
use App\Models\PosSale;
use App\Models\PosTerminal;
use App\Services\SyncProcessor;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DesktopPosSyncController extends Controller
{
    private const UNREGISTERED_TERMINAL = '[unregistered-terminal]';

    public function __invoke(Request $request, SyncProcessor $processor): JsonResponse
    {
        $request->merge(['idempotency_key' => $request->header('Idempotency-Key')]);

        $validated = $request->validate([
            'client_id' => ['required', 'string', 'max:100'],
            'machine_id' => ['required', 'string', 'max:100'],
            'entity_type' => ['required', 'string', 'in:sale,product'],
            'entity_id' => ['required', 'integer', 'min:1'],
            'operation' => ['required', 'in:create,update,delete,upsert'],
            'payload' => ['required', 'array'],
            'idempotency_key' => ['required', 'string', 'max:150'],
            'payload.sale' => ['sometimes', 'array'],
            'payload.items' => ['sometimes', 'array'],
            'payload.items.*' => ['array'],
        ]);
        $validated['payload'] = $request->input('payload');

        if ($validated['operation'] === 'upsert' && $validated['entity_type'] !== 'product') {
            throw ValidationException::withMessages([
                'operation' => 'The upsert operation is only supported for products.',
            ]);
        }

        if ($validated['entity_type'] === 'sale' && $validated['operation'] !== 'delete') {
            $request->validate([
                'payload.sale' => ['required', 'array'],
                'payload.items' => ['required', 'array'],
            ]);
        }

        if (in_array($validated['machine_id'], config('desktop_pos.special_machine_ids', []), true)) {
            $receivedAt = now();
            Log::info('Desktop POS sync request received from a special test machine.', [
                'client_id' => $validated['client_id'],
                'machine_id' => $validated['machine_id'],
                'entity_type' => $validated['entity_type'],
                'entity_id' => $validated['entity_id'],
                'operation' => $validated['operation'],
                'idempotency_key' => $validated['idempotency_key'],
                'payload' => $validated['payload'],
                'received_at' => $receivedAt->toIso8601String(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Desktop POS sync request logged; import was skipped for this test machine.',
                'data' => [
                    'sync_id' => null,
                    'client_id' => $validated['client_id'],
                    'machine_id' => $validated['machine_id'],
                    'entity_type' => $validated['entity_type'],
                    'entity_id' => $validated['entity_id'],
                    'operation' => $validated['operation'],
                    'status' => 'success',
                    'receipt_url' => null,
                    'received_at' => $receivedAt,
                ],
            ]);
        }

        /** @var PosTerminal|null $terminal */
        $terminal = $request->attributes->get('pos_terminal');

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
                'error_message' => $terminal ? null : self::UNREGISTERED_TERMINAL.' Register this exact machine_id and client_id to a business before importing this request.',
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

            if (! $this->requestIdentityMatchesLog($validated, $existing)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This Idempotency-Key was already used for a different synchronization request.',
                    'data' => [
                        'sync_id' => $existing->id,
                        'status' => $existing->status,
                    ],
                ], 409);
            }

            if ($this->requestMatchesLog($validated, $existing)) {
                if ($terminal && $existing->status !== 'success') {
                    $syncLog = $processor->process($existing);
                } else {
                    $syncLog = $existing;
                }
            } else {
                $existing->forceFill([
                    'payload' => $validated['payload'],
                    'status' => 'pending',
                    'error_message' => $terminal ? null : self::UNREGISTERED_TERMINAL.' Register this exact machine_id and client_id to a business before importing this request.',
                    'received_at' => now(),
                    'processed_at' => null,
                ])->save();

                $syncLog = $terminal ? $processor->process($existing) : $existing;
            }

            return $this->duplicateResponse($syncLog);
        }

        if ($terminal) {
            $syncLog = $processor->process($syncLog);
        }

        $message = match ($syncLog->status) {
            'success' => 'Desktop POS sync request imported successfully.',
            'pending' => 'Sync request stored as pending until the machine and client ID are registered.',
            default => 'Sync request stored but not imported; review its synchronization status.',
        };

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'sync_id' => $syncLog->id,
                'client_id' => $syncLog->client_id,
                'machine_id' => $syncLog->machine_id,
                'entity_type' => $syncLog->entity_type,
                'entity_id' => $syncLog->entity_id,
                'operation' => $syncLog->operation,
                'status' => $syncLog->status,
                'receipt_url' => $this->receiptUrl($syncLog),
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
                'receipt_url' => $this->receiptUrl($syncLog),
                'received_at' => $syncLog->received_at,
            ],
        ]);
    }

    private function receiptUrl(DesktopPosSyncLog $syncLog): ?string
    {
        if ($syncLog->status !== 'success' || $syncLog->entity_type !== 'sale' || $syncLog->operation === 'delete') {
            return null;
        }

        $sale = PosSale::query()
            ->where('machine_id', $syncLog->machine_id)
            ->where('desktop_sale_id', $syncLog->entity_id)
            ->first();

        return $sale ? route('sales.receipt', $sale) : null;
    }

    private function requestMatchesLog(array $validated, DesktopPosSyncLog $syncLog): bool
    {
        return $this->requestIdentityMatchesLog($validated, $syncLog)
            && $this->normalizePayload($syncLog->payload) === $this->normalizePayload($validated['payload']);
    }

    private function requestIdentityMatchesLog(array $validated, DesktopPosSyncLog $syncLog): bool
    {
        return $syncLog->client_id === $validated['client_id']
            && $syncLog->machine_id === $validated['machine_id']
            && $syncLog->entity_type === $validated['entity_type']
            && (int) $syncLog->entity_id === (int) $validated['entity_id']
            && $syncLog->operation === $validated['operation'];
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
