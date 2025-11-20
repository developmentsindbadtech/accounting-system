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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('bill_number')->unique()->index();
            $table->unsignedBigInteger('vendor_id');
            $table->date('bill_date')->index();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('status', ['draft', 'received', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('restrict');
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
