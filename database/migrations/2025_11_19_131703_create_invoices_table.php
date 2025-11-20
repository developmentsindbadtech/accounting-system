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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('invoice_number')->unique()->index();
            $table->unsignedBigInteger('customer_id');
            $table->date('invoice_date')->index();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->string('payment_terms')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
