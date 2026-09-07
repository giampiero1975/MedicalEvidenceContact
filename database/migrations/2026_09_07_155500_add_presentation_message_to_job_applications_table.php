<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->string('presentation_message', 500)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->dropColumn('presentation_message');
        });
    }
};
