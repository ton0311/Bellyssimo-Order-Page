<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_notification_settings', function (Blueprint $table): void {
            $table->id();
            $table->json('recipient_emails');
            $table->timestamps();
        });

        DB::table('order_notification_settings')->insert([
            'id' => 1,
            'recipient_emails' => json_encode(config('services.resend.order_notifications.emails', []), JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('order_notification_settings');
    }
};
