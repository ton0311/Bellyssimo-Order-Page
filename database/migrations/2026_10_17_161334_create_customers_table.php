<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('contact_number', 30)->unique();
            $table->text('notes')->nullable();
            $table->timestamp('last_order_at')->nullable()->index();
            $table->timestamps();
        });

        DB::table('orders')
            ->orderBy('submitted_at')
            ->each(function (object $order): void {
                DB::table('customers')->updateOrInsert(
                    ['contact_number' => $order->contact_number],
                    [
                        'name' => $order->customer_name,
                        'last_order_at' => $order->submitted_at,
                        'created_at' => $order->submitted_at,
                        'updated_at' => $order->submitted_at,
                    ],
                );
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
