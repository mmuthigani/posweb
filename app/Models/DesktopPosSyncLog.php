<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesktopPosSyncLog extends Model
{
    protected $table = 'desktop_pos_sync_logs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class, 'machine_id', 'machine_id');
    }
}
