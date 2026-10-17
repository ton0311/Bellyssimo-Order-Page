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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('submitted_at')->index();
        });

        DB::table('orders')
            ->whereIn('status', ['In preparation', 'Ready for pickup'])
            ->update(['status' => 'Confirmed']);

        DB::table('orders')
            ->where('status', 'Completed')
            ->update([
                'payment_status' => 'Paid',
                'completed_at' => DB::raw('COALESCE(submitted_at, updated_at, CURRENT_TIMESTAMP)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
