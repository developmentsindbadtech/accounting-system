<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('sku')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['product', 'service']);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('purchase_account_id')->nullable();
            $table->unsignedBigInteger('sales_account_id')->nullable();
            $table->unsignedBigInteger('inventory_account_id')->nullable();
            $table->string('unit_of_measure')->default('Unit');
            $table->boolean('track_quantity')->default(false);
            $table->decimal('quantity_on_hand', 10, 2)->default(0);
            $table->decimal('quantity_reserved', 10, 2)->default(0);
            $table->decimal('reorder_point', 10, 2)->nullable();
            $table->decimal('standard_cost', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('item_categories')->onDelete('set null');
            $table->foreign('purchase_account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('sales_account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('inventory_account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->index(['tenant_id', 'sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
