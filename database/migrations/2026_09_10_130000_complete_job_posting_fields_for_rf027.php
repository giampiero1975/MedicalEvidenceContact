<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->string('professional_category', 40)->nullable()->after('description');
            $table->string('workplace_city', 150)->nullable()->after('workplace_address');
            $table->string('workplace_province', 100)->nullable()->after('workplace_city');
            $table->text('benefits')->nullable()->after('required_skills');
            $table->text('preferred_requirements')->nullable()->after('benefits');
            $table->string('work_schedule', 255)->nullable()->after('preferred_requirements');
        });
    }

    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn([
                'professional_category',
                'workplace_city',
                'workplace_province',
                'benefits',
                'preferred_requirements',
                'work_schedule',
            ]);
        });
    }
};
