<?php

namespace App\Services;

use App\Models\DesktopPosSyncLog;
use App\Models\PosSale;
use App\Models\PosTerminal;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SyncProcessor
{
    public function process(DesktopPosSyncLog $syncLog): DesktopPosSyncLog
    {
        if ($syncLog->status === 'success') {
            return $syncLog;
        }

        try {
            DB::transaction(function () use ($syncLog): void {
                $log = DesktopPosSyncLog::query()->lockForUpdate()->findOrFail($syncLog->id);

                if ($log->status === 'success') {
                    return;
                }

                $log->forceFill([
                    'status' => 'processing',
                    'error_message' => null,
                    'processed_at' => null,
                ])->save();

                $terminal = PosTerminal::query()
                    ->where('machine_id', $log->machine_id)
                    ->where('is_active', true)
                    ->firstOrFail();

                if ($log->entity_type === 'sale') {
                    $this->processSale($log, $terminal);
                } elseif ($log->entity_type === 'product') {
                    $this->processProducts($log, $terminal);
                } else {
                    throw new RuntimeException("Unsupported synchronized entity: {$log->entity_type}.");
                }

                $terminal->forceFill(['last_seen_at' => now()])->save();
                $log->forceFill([
                    'status' => 'success',
                    'error_message' => null,
                    'processed_at' => now(),
                ])->save();
            });
        } catch (Throwable $exception) {
            report($exception);

            DesktopPosSyncLog::query()->whereKey($syncLog->id)->update([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 5000),
                'processed_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $syncLog->fresh();
    }

    private function processSale(DesktopPosSyncLog $log, PosTerminal $terminal): void
    {
        if ($log->operation === 'delete') {
            PosSale::query()
                ->where('business_id', $terminal->business_id)
                ->where('terminal_id', $terminal->id)
                ->where('desktop_sale_id', $log->entity_id)
                ->delete();

            return;
        }

        $saleData = data_get($log->payload, 'sale');

        if (! is_array($saleData) || (int) ($saleData['id'] ?? 0) !== (int) $log->entity_id) {
            throw new RuntimeException('Sale payload is missing or its ID does not match entity_id.');
        }

        $sale = PosSale::query()->updateOrCreate(
            [
                'business_id' => $terminal->business_id,
                'terminal_id' => $terminal->id,
                'desktop_sale_id' => $log->entity_id,
            ],
            [
                'source_sync_log_id' => $log->id,
                'machine_id' => $terminal->machine_id,
                'receipt_no' => $saleData['receipt_no'] ?? null,
                'subtotal' => $saleData['subtotal'] ?? 0,
                'total' => $saleData['total'] ?? 0,
                'amount_paid' => $saleData['amount_paid'] ?? 0,
                'change_due' => $saleData['change_due'] ?? 0,
                'payment_method' => $saleData['payment_method'] ?? null,
                'cashier_id' => $saleData['cashier_id'] ?? null,
                'cashier_name' => $saleData['cashier_name'] ?? null,
                'sold_at' => isset($saleData['created_at']) ? Carbon::parse($saleData['created_at']) : $log->received_at,
            ],
        );

        if (! is_array($log->payload['items'] ?? null)) {
            throw new RuntimeException('Sale payload must include an items array.');
        }

        $sale->items()->delete();

        foreach ($log->payload['items'] as $item) {
            if (! is_array($item) || (int) ($item['sale_id'] ?? $log->entity_id) !== (int) $log->entity_id) {
                throw new RuntimeException('Sale contains an invalid item or mismatched sale_id.');
            }

            $sale->items()->create([
                'desktop_item_id' => $item['id'] ?? null,
                'product_id' => $item['product_id'] ?? null,
                'product_name' => $item['product_name'] ?? 'Unknown product',
                'quantity' => $item['quantity'] ?? 0,
                'unit_price' => $item['unit_price'] ?? 0,
                'line_total' => $item['line_total'] ?? 0,
            ]);
        }
    }

    private function processProducts(DesktopPosSyncLog $log, PosTerminal $terminal): void
    {
        if ($log->operation === 'delete') {
            Product::query()
                ->where('business_id', $terminal->business_id)
                ->where('desktop_product_id', $log->entity_id)
                ->delete();

            return;
        }

        $payload = $log->payload;
        $records = $payload['products'] ?? $payload['product'] ?? $payload['data'] ?? $payload;

        if (isset($records['id']) || isset($records['product_id'])) {
            $records = [$records];
        }

        if (! is_array($records) || $records === []) {
            throw new RuntimeException('Product payload does not contain a product record.');
        }

        foreach ($records as $record) {
            if (! is_array($record)) {
                throw new RuntimeException('Product payload contains an invalid record.');
            }

            $desktopId = (int) ($record['id'] ?? $record['product_id'] ?? 0);
            $name = $record['name'] ?? $record['product_name'] ?? null;

            if ($desktopId < 1 || ! is_string($name) || trim($name) === '') {
                throw new RuntimeException('Each product must include an ID and a name.');
            }

            Product::query()->updateOrCreate(
                [
                    'business_id' => $terminal->business_id,
                    'desktop_product_id' => $desktopId,
                ],
                [
                    'terminal_id' => $terminal->id,
                    'name' => $name,
                    'sku' => $record['sku'] ?? $record['product_code'] ?? null,
                    'barcode' => $record['barcode'] ?? null,
                    'unit_price' => $record['unit_price'] ?? $record['selling_price'] ?? $record['price'] ?? null,
                    'stock_quantity' => $record['stock_quantity'] ?? $record['quantity'] ?? null,
                    'payload' => $record,
                    'synced_at' => now(),
                ],
            );
        }
    }
}
