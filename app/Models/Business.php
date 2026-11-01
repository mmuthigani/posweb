<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    protected $fillable = [
        'name',
        'business_code',
        'email',
        'phone',
        'address',
        'currency',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function terminals(): HasMany
    {
        return $this->hasMany(PosTerminal::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(PosSale::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
