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
        Schema::create('depreciation_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('fixed_asset_id');
            $table->string('period'); // e.g., "2025-01"
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2);
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->timestamp('created_at');

            $table->foreign('fixed_asset_id')->references('id')->on('fixed_assets')->onDelete('cascade');
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onDelete('set null');
            $table->index(['tenant_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depreciation_entries');
    }
};
