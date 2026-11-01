<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('desktop_pos_sync_logs')) {
            return;
        }

        Schema::table('desktop_pos_sync_logs', function (Blueprint $table): void {
            $table->index(['machine_id', 'received_at'], 'desktop_pos_machine_received_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('desktop_pos_sync_logs')) {
            return;
        }

        Schema::table('desktop_pos_sync_logs', function (Blueprint $table): void {
            $table->dropIndex('desktop_pos_machine_received_index');
        });
    }
};
