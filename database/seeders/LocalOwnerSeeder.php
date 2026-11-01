<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class LocalOwnerSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('The local owner account seeder is only available in local and testing environments.');
        }

        $business = Business::query()->firstOrCreate(
            ['business_code' => 'KIKIMO-LOCAL'],
            [
                'name' => 'Kikimo POS Demo',
                'currency' => 'KES',
                'is_active' => true,
            ],
        );

        $user = User::query()->firstOrCreate(
            ['email' => 'admin@kikimo.local'],
            [
                'business_id' => $business->id,
                'name' => 'Kikimo Administrator',
                'password' => '12345678',
                'role' => 'owner',
                'is_active' => true,
            ],
        );

        if ($user->business_id !== $business->id || $user->role !== 'owner') {
            throw new RuntimeException('The local owner email is already assigned to a different account.');
        }
    }
}
