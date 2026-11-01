<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('desktop_pos_sync_logs', function (Blueprint $table) {

            $table->index(
                ['machine_id', 'received_at'],
                'sync_logs_machine_received_index'
            );

            $table->index(
                ['machine_id', 'status', 'received_at'],
                'sync_logs_machine_status_received_index'
            );

            $table->index(
                ['machine_id', 'entity_type', 'received_at'],
                'sync_logs_machine_entity_received_index'
            );

        });
    }

    public function down(): void
    {
        Schema::table('desktop_pos_sync_logs', function (Blueprint $table) {

            $table->dropIndex('sync_logs_machine_received_index');

            $table->dropIndex('sync_logs_machine_status_received_index');

            $table->dropIndex('sync_logs_machine_entity_received_index');

        });
    }
};