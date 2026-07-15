<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table): void {
            $table->boolean('contact_sharing_consent')->default(false)->after('status');
            $table->timestamp('responded_at')->nullable()->after('contact_sharing_consent');
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table): void {
            $table->dropColumn(['contact_sharing_consent', 'responded_at']);
        });
    }
};
