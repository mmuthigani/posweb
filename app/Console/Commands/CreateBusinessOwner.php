<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateBusinessOwner extends Command
{
    protected $signature = 'kikimo:create-owner';

    protected $description = 'Create a business and its first owner without public registration';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Business name'));
        $code = Str::upper(trim((string) $this->ask('Unique business code')));
        $ownerName = trim((string) $this->ask('Owner name'));
        $email = Str::lower(trim((string) $this->ask('Owner email')));
        $password = (string) $this->secret('Owner password (minimum 12 characters)');

        if ($name === '' || $ownerName === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
            $this->error('Provide a business name, owner name, valid email, and a password of at least 12 characters.');

            return self::FAILURE;
        }

        if ($code === '' || Business::query()->where('business_code', $code)->exists() || User::query()->where('email', $email)->exists()) {
            $this->error('The business code or owner email is already in use.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($name, $code, $ownerName, $email, $password): void {
            $business = Business::query()->create([
                'name' => $name,
                'business_code' => $code,
                'currency' => 'KES',
                'is_active' => true,
            ]);

            User::query()->create([
                'business_id' => $business->id,
                'name' => $ownerName,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'owner',
            ]);
        });

        $this->info('Business and owner created. Sign in at /login.');

        return self::SUCCESS;
    }
}
