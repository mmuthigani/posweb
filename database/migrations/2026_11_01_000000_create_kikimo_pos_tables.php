<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('businesses')) {
            Schema::create('businesses', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('business_code')->unique();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->string('currency', 3)->default('KES');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('users')) {
            throw new RuntimeException('The existing users table is required before Kikimo POS can be installed.');
        }

        if (! Schema::hasColumn('users', 'business_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('staff')->index();
            });
        }

        if (! Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_active')->default(true);
            });
        }

        if (! Schema::hasTable('pos_terminals')) {
            Schema::create('pos_terminals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained()->cascadeOnDelete();
                $table->string('machine_id', 100)->unique();
                $table->string('client_id', 100)->nullable();
                $table->string('terminal_name');
                $table->string('api_token_hash', 64)->nullable()->unique();
                $table->timestamp('last_seen_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['business_id', 'is_active']);
            });
        }

        if (! Schema::hasTable('pos_sales')) {
            Schema::create('pos_sales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained()->cascadeOnDelete();
                $table->foreignId('terminal_id')->constrained('pos_terminals')->cascadeOnDelete();
                $table->foreignId('source_sync_log_id')->unique()->constrained('desktop_pos_sync_logs');
                $table->string('machine_id', 100);
                $table->unsignedBigInteger('desktop_sale_id');
                $table->string('receipt_no')->nullable()->index();
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->decimal('change_due', 12, 2)->default(0);
                $table->string('payment_method', 50)->nullable()->index();
                $table->unsignedBigInteger('cashier_id')->nullable()->index();
                $table->string('cashier_name')->nullable()->index();
                $table->timestamp('sold_at')->nullable()->index();
                $table->timestamps();
                $table->unique(['business_id', 'terminal_id', 'desktop_sale_id'], 'pos_sales_desktop_identity_unique');
                $table->index(['business_id', 'sold_at']);
            });
        }

        if (! Schema::hasTable('pos_sale_items')) {
            Schema::create('pos_sale_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pos_sale_id')->constrained('pos_sales')->cascadeOnDelete();
                $table->unsignedBigInteger('desktop_item_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('product_name');
                $table->decimal('quantity', 12, 3)->default(0);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('line_total', 12, 2)->default(0);
                $table->timestamps();
                $table->index(['pos_sale_id', 'product_id']);
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained()->cascadeOnDelete();
                $table->foreignId('terminal_id')->nullable()->constrained('pos_terminals')->nullOnDelete();
                $table->unsignedBigInteger('desktop_product_id');
                $table->string('name');
                $table->string('sku')->nullable()->index();
                $table->string('barcode')->nullable()->index();
                $table->decimal('unit_price', 12, 2)->nullable();
                $table->decimal('stock_quantity', 12, 3)->nullable();
                $table->json('payload');
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
                $table->unique(['business_id', 'desktop_product_id'], 'products_desktop_identity_unique');
                $table->index(['business_id', 'name']);
            });
        }
    }

    public function down(): void
    {
        // This migration may adopt tables that existed before it ran. Keep rollback non-destructive.
    }
};
