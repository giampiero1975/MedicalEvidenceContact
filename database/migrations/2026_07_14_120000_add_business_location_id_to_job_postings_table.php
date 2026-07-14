<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->foreignId('business_location_id')
                ->nullable()
                ->after('business_profile_id')
                ->constrained('business_locations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_location_id');
        });
    }
};
