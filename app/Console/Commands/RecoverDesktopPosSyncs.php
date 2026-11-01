<?php

namespace App\Console\Commands;

use App\Models\DesktopPosSyncLog;
use App\Services\SyncProcessor;
use Illuminate\Console\Command;

class RecoverDesktopPosSyncs extends Command
{
    protected $signature = 'kikimo:recover-syncs {--limit=100 : Maximum sync records to retry}';

    protected $description = 'Retry pending, interrupted, and failed desktop POS imports';

    public function handle(SyncProcessor $processor): int
    {
        $limit = max(1, min((int) $this->option('limit'), 1000));
        $logs = DesktopPosSyncLog::query()
            ->whereIn('status', ['pending', 'processing', 'failed'])
            ->where(function ($query): void {
                $query->where(function ($query): void {
                    $query->whereNull('error_message')
                        ->orWhere(function ($query): void {
                            $query->where('error_message', 'not like', '[awaiting-auth]%')
                                ->where('error_message', 'not like', '[unregistered-terminal]%');
                        });
                })->orWhereExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('pos_terminals')
                        ->whereColumn('pos_terminals.machine_id', 'desktop_pos_sync_logs.machine_id')
                        ->whereColumn('pos_terminals.client_id', 'desktop_pos_sync_logs.client_id')
                        ->where('pos_terminals.is_active', true);
                });
            })
            ->where('received_at', '<=', now()->subMinute())
            ->orderBy('received_at')
            ->limit($limit)
            ->get();

        foreach ($logs as $log) {
            $processor->process($log);
        }

        $this->info("Processed {$logs->count()} recoverable sync record(s).");

        return self::SUCCESS;
    }
}
