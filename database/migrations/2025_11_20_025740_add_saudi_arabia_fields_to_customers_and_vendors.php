<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds Saudi Arabia specific fields for IFRS and ZATCA compliance
     */
    public function up(): void
    {
        // Update customers table
        Schema::table('customers', function (Blueprint $table) {
            // Saudi Arabia specific fields
            $table->string('commercial_registration_number')->nullable()->after('tax_id');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->string('country')->default('Saudi Arabia')->after('postal_code');
            $table->string('mobile')->nullable()->after('phone');
            $table->string('contact_person')->nullable()->after('name');
            $table->string('company_name')->nullable()->after('name');
            $table->string('billing_address')->nullable()->after('address');
            $table->string('shipping_address')->nullable()->after('billing_address');
            $table->string('currency', 3)->default('SAR')->after('credit_limit');
            $table->string('language_preference', 10)->default('en')->after('currency');
            $table->text('notes')->nullable()->after('is_active');
        });

        // Update vendors table
        Schema::table('vendors', function (Blueprint $table) {
            // Saudi Arabia specific fields
            $table->string('commercial_registration_number')->nullable()->after('tax_id');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->string('country')->default('Saudi Arabia')->after('postal_code');
            $table->string('mobile')->nullable()->after('phone');
            $table->string('contact_person')->nullable()->after('name');
            $table->string('company_name')->nullable()->after('name');
            $table->string('billing_address')->nullable()->after('address');
            $table->string('currency', 3)->default('SAR')->after('payment_terms');
            $table->text('notes')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'commercial_registration_number',
                'city',
                'state',
                'postal_code',
                'country',
                'mobile',
                'contact_person',
                'company_name',
                'billing_address',
                'shipping_address',
                'currency',
                'language_preference',
                'notes'
            ]);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'commercial_registration_number',
                'city',
                'state',
                'postal_code',
                'country',
                'mobile',
                'contact_person',
                'company_name',
                'billing_address',
                'currency',
                'notes'
            ]);
        });
    }
};
