<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('business_profiles', 'address_street')) {
                $table->string('address_street')->nullable();
            }
            if (! Schema::hasColumn('business_profiles', 'address_city')) {
                $table->string('address_city', 150)->nullable();
            }
            if (! Schema::hasColumn('business_profiles', 'address_province')) {
                $table->string('address_province', 100)->nullable();
            }
            if (! Schema::hasColumn('business_profiles', 'postal_code')) {
                $table->string('postal_code', 20)->nullable();
            }
            if (! Schema::hasColumn('business_profiles', 'address_country')) {
                $table->string('address_country', 150)->nullable();
            }
        });

        Schema::table('business_points_of_contact', function (Blueprint $table) {
            if (! Schema::hasColumn('business_points_of_contact', 'role')) {
                $table->string('role', 150)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_points_of_contact', function (Blueprint $table) {
            if (Schema::hasColumn('business_points_of_contact', 'role')) {
                $table->dropColumn('role');
            }
        });

        Schema::table('business_profiles', function (Blueprint $table) {
            $columns = collect(['address_street', 'address_city', 'address_province', 'postal_code', 'address_country'])
                ->filter(fn (string $column) => Schema::hasColumn('business_profiles', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
