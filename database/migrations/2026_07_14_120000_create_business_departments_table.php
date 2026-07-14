<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_departments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_location_id')->constrained()->cascadeOnDelete();
            $table->string('name', 180);
            $table->string('code', 60)->nullable();
            $table->string('manager_name', 180)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['business_profile_id', 'is_active']);
            $table->unique(['business_location_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_departments');
    }
};
