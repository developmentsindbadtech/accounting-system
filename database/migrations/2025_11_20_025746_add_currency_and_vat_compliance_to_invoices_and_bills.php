<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds currency and ZATCA VAT compliance fields for Saudi Arabia
     */
    public function up(): void
    {
        // Update invoices table
        Schema::table('invoices', function (Blueprint $table) {
            // Currency support (default SAR for Saudi Arabia)
            $table->string('currency', 3)->default('SAR')->after('total');
            $table->decimal('exchange_rate', 10, 6)->default(1.0)->after('currency');
            
            // ZATCA VAT compliance fields
            $table->string('tax_invoice_number')->nullable()->unique()->after('invoice_number');
            $table->string('qr_code')->nullable()->after('tax_invoice_number');
            $table->string('invoice_type')->default('standard')->after('status'); // standard, proforma, credit_memo, debit_memo
            $table->string('sales_representative')->nullable()->after('customer_id');
            
            // Additional IFRS compliance fields
            $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('taxable_amount', 15, 2)->default(0)->after('discount_amount');
            $table->decimal('amount_paid', 15, 2)->default(0)->after('total');
            $table->decimal('balance_due', 15, 2)->default(0)->after('amount_paid');
        });

        // Update bills table
        Schema::table('bills', function (Blueprint $table) {
            // Currency support (default SAR for Saudi Arabia)
            $table->string('currency', 3)->default('SAR')->after('total');
            $table->decimal('exchange_rate', 10, 6)->default(1.0)->after('currency');
            
            // ZATCA VAT compliance fields
            $table->string('tax_invoice_number')->nullable()->unique()->after('bill_number');
            $table->string('qr_code')->nullable()->after('tax_invoice_number');
            
            // Additional IFRS compliance fields
            $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('taxable_amount', 15, 2)->default(0)->after('discount_amount');
            $table->decimal('amount_paid', 15, 2)->default(0)->after('total');
            $table->decimal('balance_due', 15, 2)->default(0)->after('amount_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'currency',
                'exchange_rate',
                'tax_invoice_number',
                'qr_code',
                'invoice_type',
                'sales_representative',
                'discount_amount',
                'taxable_amount',
                'amount_paid',
                'balance_due'
            ]);
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn([
                'currency',
                'exchange_rate',
                'tax_invoice_number',
                'qr_code',
                'discount_amount',
                'taxable_amount',
                'amount_paid',
                'balance_due'
            ]);
        });
    }
};
