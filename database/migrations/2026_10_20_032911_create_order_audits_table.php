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
        Schema::create('order_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_reference')->index();
            $table->string('event', 80)->index();
            $table->string('actor_type', 40);
            $table->string('actor_identifier')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_audits');
    }
};
