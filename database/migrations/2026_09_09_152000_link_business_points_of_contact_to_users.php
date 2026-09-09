<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_points_of_contact', function (Blueprint $table) {
            if (! Schema::hasColumn('business_points_of_contact', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->unique()
                    ->after('business_profile_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_points_of_contact', function (Blueprint $table) {
            if (Schema::hasColumn('business_points_of_contact', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
