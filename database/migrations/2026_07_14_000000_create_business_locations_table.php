<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_profile_id')->constrained()->cascadeOnDelete();
            $table->string('name', 180);
            $table->enum('type', ['legal', 'operational'])->default('operational');
            $table->string('street_address');
            $table->string('city', 120);
            $table->string('province', 10)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 120)->default('Italia');
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['business_profile_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_locations');
    }
};
