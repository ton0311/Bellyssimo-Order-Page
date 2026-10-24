<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resend_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('webhook_event_id')->unique();
            $table->uuid('resend_email_id')->index();
            $table->string('event_type', 80)->index();
            $table->timestamp('occurred_at')->index();
            $table->json('payload');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resend_webhook_events');
    }
};
