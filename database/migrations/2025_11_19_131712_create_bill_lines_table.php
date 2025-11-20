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
        Schema::create('bill_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->text('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->decimal('vat_percent', 5, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('account_id');
            $table->timestamps();

            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_lines');
    }
};
