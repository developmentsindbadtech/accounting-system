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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('payment_number')->unique()->index();
            $table->unsignedBigInteger('customer_id');
            $table->date('payment_date')->index();
            $table->string('payment_method');
            $table->string('reference')->nullable();
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at');

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('restrict');
            $table->index(['tenant_id', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
