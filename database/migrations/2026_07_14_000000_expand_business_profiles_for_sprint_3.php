<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_profiles', function (Blueprint $table): void {
            $table->string('legal_name')->nullable()->after('company_name');
            $table->string('vat_number', 32)->nullable()->after('company_type');
            $table->string('tax_code', 32)->nullable()->after('vat_number');
            $table->text('description')->nullable()->after('tax_code');
            $table->string('website')->nullable()->after('description');
            $table->string('email')->nullable()->after('website');
            $table->string('phone', 40)->nullable()->after('email');
            $table->string('pec')->nullable()->after('phone');
            $table->string('logo_path')->nullable()->after('pec');
        });
    }

    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'legal_name',
                'vat_number',
                'tax_code',
                'description',
                'website',
                'email',
                'phone',
                'pec',
                'logo_path',
            ]);
        });
    }
};
