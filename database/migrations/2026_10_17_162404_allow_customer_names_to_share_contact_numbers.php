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
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['contact_number']);
            $table->unique(['name', 'contact_number']);
        });

        DB::table('orders')
            ->orderBy('submitted_at')
            ->each(function (object $order): void {
                DB::table('customers')->updateOrInsert(
                    [
                        'name' => $order->customer_name,
                        'contact_number' => $order->contact_number,
                    ],
                    [
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
        $duplicates = DB::table('customers')
            ->select('contact_number')
            ->groupBy('contact_number')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('contact_number');

        foreach ($duplicates as $contactNumber) {
            $keepId = DB::table('customers')
                ->where('contact_number', $contactNumber)
                ->orderByDesc('last_order_at')
                ->value('id');

            DB::table('customers')
                ->where('contact_number', $contactNumber)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['name', 'contact_number']);
            $table->unique('contact_number');
        });
    }
};
