<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('desktop_pos_sync_logs')) {
            return;
        }

        Schema::create('desktop_pos_sync_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('client_id', 100);
            $table->string('machine_id', 100)->index();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->string('operation', 20);
            $table->json('payload');
            $table->string('idempotency_key', 150)->nullable();
            $table->enum('status', ['pending', 'processing', 'success', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['machine_id', 'idempotency_key'], 'desktop_pos_machine_idempotency_unique');
            $table->index(['machine_id', 'entity_type', 'entity_id'], 'desktop_pos_entity_index');
        });
    }

    public function down(): void
    {
        // The synchronization table and its records must never be dropped by a rollback.
    }
};
