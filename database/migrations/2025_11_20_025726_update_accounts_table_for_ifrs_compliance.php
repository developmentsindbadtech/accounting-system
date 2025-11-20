<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates accounts table for IFRS compliance with Current/Non-Current distinction
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Add sub_type for IFRS compliance (Current/Non-Current distinction)
            $table->enum('sub_type', [
                'Current Asset',
                'Non-Current Asset',
                'Current Liability',
                'Non-Current Liability',
                'Equity',
                'Revenue',
                'Expense',
                'Cost of Goods Sold'
            ])->nullable()->after('type');
            
            // Add description/notes field
            $table->text('description')->nullable()->after('name');
            
            // Add opening balance field for proper IFRS reporting
            $table->decimal('opening_balance', 15, 2)->default(0)->after('balance');
        });

        // Update existing accounts to have appropriate sub_types based on their types
        // This is a data migration to maintain IFRS compliance
        DB::statement("
            UPDATE accounts 
            SET sub_type = CASE 
                WHEN type = 'Asset' THEN 'Current Asset'
                WHEN type = 'Liability' THEN 'Current Liability'
                WHEN type = 'Equity' THEN 'Equity'
                WHEN type = 'Revenue' THEN 'Revenue'
                WHEN type = 'Expense' THEN 'Expense'
                ELSE NULL
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['sub_type', 'description', 'opening_balance']);
        });
    }
};
