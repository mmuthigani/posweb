<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'business_id',
        'pos_terminal_id',
        'phone',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function posTerminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class);
    }

    public function posTerminals(): BelongsToMany
    {
        return $this->belongsToMany(PosTerminal::class)->withTimestamps();
    }

    /**
     * @return list<int>
     */
    public function assignedTerminalIds(): array
    {
        $terminalIds = $this->posTerminals()
            ->where('pos_terminals.business_id', $this->business_id)
            ->where('pos_terminals.is_active', true)
            ->pluck('pos_terminals.id')
            ->all();

        if ($terminalIds === [] && $this->pos_terminal_id) {
            $legacyTerminal = PosTerminal::query()
                ->whereKey($this->pos_terminal_id)
                ->where('business_id', $this->business_id)
                ->where('is_active', true)
                ->value('id');

            if ($legacyTerminal) {
                $terminalIds[] = $legacyTerminal;
            }
        }

        return array_map('intval', $terminalIds);
    }

    public function isAdministrator(): bool
    {
        return in_array($this->role, ['owner', 'admin'], true);
    }
}
