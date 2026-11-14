<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'business_id',
        'terminal_id',
        'desktop_product_id',
        'name',
        'sku',
        'barcode',
        'unit_price',
        'stock_quantity',
        'payload',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'stock_quantity' => 'decimal:3',
            'payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function getImageDataUriAttribute(): ?string
    {
        foreach ($this->payload ?? [] as $key => $value) {
            if (! preg_match('/image|photo|picture/i', (string) $key) || ! is_string($value)) {
                continue;
            }

            if (preg_match('/\Adata:image\/(?:png|jpe?g|gif|webp);base64,(.*)\z/is', $value, $matches)) {
                $value = $matches[1];
            }

            $encoded = preg_replace('/\s+/', '', $value);
            if ($encoded === '' || strlen($encoded) > 14_000_000) {
                continue;
            }

            $image = base64_decode($encoded, true);
            if ($image === false) {
                continue;
            }

            $mimeType = match (true) {
                str_starts_with($image, "\x89PNG\r\n\x1a\n") => 'image/png',
                str_starts_with($image, "\xff\xd8\xff") => 'image/jpeg',
                str_starts_with($image, 'GIF87a'), str_starts_with($image, 'GIF89a') => 'image/gif',
                strlen($image) >= 12 && substr($image, 0, 4) === 'RIFF' && substr($image, 8, 4) === 'WEBP' => 'image/webp',
                default => null,
            };

            if ($mimeType !== null) {
                return "data:{$mimeType};base64,".base64_encode($image);
            }
        }

        return null;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class, 'terminal_id');
    }
}
