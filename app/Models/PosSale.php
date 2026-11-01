<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSale extends Model
{
    protected $fillable = [
        'business_id',
        'terminal_id',
        'source_sync_log_id',
        'machine_id',
        'desktop_sale_id',
        'receipt_no',
        'subtotal',
        'total',
        'amount_paid',
        'change_due',
        'payment_method',
        'cashier_id',
        'cashier_name',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'change_due' => 'decimal:2',
            'sold_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class, 'terminal_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
    }

    public function syncLog(): BelongsTo
    {
        return $this->belongsTo(DesktopPosSyncLog::class, 'source_sync_log_id');
    }
}
