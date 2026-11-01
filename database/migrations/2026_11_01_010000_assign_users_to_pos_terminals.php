<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'pos_terminal_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('pos_terminal_id')
                    ->nullable()
                    ->after('business_id')
                    ->constrained('pos_terminals')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Keep account assignments when rolling back an application deployment.
    }
};
