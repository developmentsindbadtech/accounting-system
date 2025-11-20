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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code')->index();
            $table->string('name');
            $table->enum('type', ['Asset', 'Liability', 'Equity', 'Revenue', 'Expense']);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['tenant_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
