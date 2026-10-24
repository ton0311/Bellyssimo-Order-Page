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
        Schema::create('order_email_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_reference')->index();
            $table->string('email_kind', 60)->index();
            $table->string('idempotency_key')->unique();
            $table->uuid('resend_email_id')->nullable()->unique();
            $table->string('status', 40)->default('pending')->index();
            $table->string('latest_event_type', 80)->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('last_event_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_email_deliveries');
    }
};
