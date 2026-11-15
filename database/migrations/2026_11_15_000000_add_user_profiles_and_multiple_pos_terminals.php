<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone', 50)->nullable()->after('email');
            });
        }

        if (! Schema::hasTable('pos_terminal_user')) {
            Schema::create('pos_terminal_user', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('pos_terminal_id')->constrained('pos_terminals')->cascadeOnDelete();
                $table->timestamps();
                $table->primary(['user_id', 'pos_terminal_id']);
            });
        }

        if (Schema::hasColumn('users', 'pos_terminal_id')) {
            DB::table('users')
                ->whereNotNull('pos_terminal_id')
                ->orderBy('id')
                ->get(['id', 'pos_terminal_id'])
                ->each(function (object $user): void {
                    DB::table('pos_terminal_user')->insertOrIgnore([
                        'user_id' => $user->id,
                        'pos_terminal_id' => $user->pos_terminal_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
        }
    }

    public function down(): void
    {
        // Keep profile details and machine assignments when rolling back an application deployment.
    }
};
