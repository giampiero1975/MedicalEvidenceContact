<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('business_points_of_contact', 'is_primary')) {
            Schema::table('business_points_of_contact', function (Blueprint $table) {
                $table->boolean('is_primary')->default(false)->after('role');
            });
        }

        DB::table('business_profiles')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($profiles): void {
                foreach ($profiles as $profile) {
                    $firstPocId = DB::table('business_points_of_contact')
                        ->where('business_profile_id', $profile->id)
                        ->orderBy('id')
                        ->value('id');

                    if ($firstPocId) {
                        DB::table('business_points_of_contact')
                            ->where('business_profile_id', $profile->id)
                            ->update(['is_primary' => false]);

                        DB::table('business_points_of_contact')
                            ->where('id', $firstPocId)
                            ->update(['is_primary' => true]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('business_points_of_contact', 'is_primary')) {
            Schema::table('business_points_of_contact', function (Blueprint $table) {
                $table->dropColumn('is_primary');
            });
        }
    }
};
