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
        Schema::create('vat_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('vat_code_id');
            $table->string('transaction_type'); // e.g., 'invoice', 'bill'
            $table->string('reference_type'); // e.g., 'App\Models\Invoice'
            $table->unsignedBigInteger('reference_id');
            $table->decimal('vat_amount', 15, 2);
            $table->decimal('net_amount', 15, 2);
            $table->decimal('gross_amount', 15, 2);
            $table->date('transaction_date')->index();
            $table->timestamp('created_at');

            $table->foreign('vat_code_id')->references('id')->on('vat_codes')->onDelete('restrict');
            $table->index(['tenant_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vat_transactions');
    }
};
