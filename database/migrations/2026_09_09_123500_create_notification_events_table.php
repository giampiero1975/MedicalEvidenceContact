<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 80);
            $table->string('frequency', 20);
            $table->string('subject');
            $table->string('heading');
            $table->text('intro');
            $table->string('action_label');
            $table->text('action_url');
            $table->json('details')->nullable();
            $table->string('secondary_action_label')->nullable();
            $table->text('secondary_action_url')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'frequency', 'delivered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_events');
    }
};
