<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesktopPosSyncLog extends Model
{
    protected $table = 'desktop_pos_sync_logs';

    protected $fillable = [
        'client_id',
        'machine_id',
        'entity_type',
        'entity_id',
        'operation',
        'payload',
        'idempotency_key',
        'status',
        'error_message',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class, 'machine_id', 'machine_id');
    }
}
