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
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->text('attachments')->nullable()->after('disposal_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
