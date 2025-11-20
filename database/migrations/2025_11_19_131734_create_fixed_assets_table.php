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
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('asset_number')->unique()->index();
            $table->string('name');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2);
            $table->integer('useful_life_years');
            $table->enum('depreciation_method', ['straight-line', 'reducing-balance'])->default('straight-line');
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->decimal('net_book_value', 15, 2);
            $table->unsignedBigInteger('asset_account_id');
            $table->unsignedBigInteger('depreciation_expense_account_id');
            $table->unsignedBigInteger('accumulated_depreciation_account_id');
            $table->enum('status', ['active', 'disposed'])->default('active');
            $table->date('disposal_date')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('set null');
            $table->foreign('asset_account_id')->references('id')->on('accounts')->onDelete('restrict');
            $table->foreign('depreciation_expense_account_id')->references('id')->on('accounts')->onDelete('restrict');
            $table->foreign('accumulated_depreciation_account_id')->references('id')->on('accounts')->onDelete('restrict');
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
