<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table): void {
            $table->timestamp('expiry_reminder_sent_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table): void {
            $table->dropColumn('expiry_reminder_sent_at');
        });
    }
};
