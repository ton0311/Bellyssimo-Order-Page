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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->json('items');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('package_subtotal');
            $table->unsignedInteger('spicy_subtotal')->default(0);
            $table->unsignedInteger('order_total');
            $table->string('fulfillment');
            $table->string('delivery_location', 300)->nullable();
            $table->string('customer_name', 100);
            $table->string('contact_number', 30);
            $table->date('preferred_date');
            $table->time('preferred_time');
            $table->text('notes')->nullable();
            $table->string('status')->default('Pending confirmation')->index();
            $table->string('payment_status')->default('Unpaid');
            $table->timestamp('submitted_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
