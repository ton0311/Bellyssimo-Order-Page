<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('orders')->orderBy('id')->each(function (object $order): void {
            $normalized = $this->normalizePhone((string) $order->contact_number);

            if ($normalized !== $order->contact_number) {
                DB::table('orders')->where('id', $order->id)->update(['contact_number' => $normalized]);
            }
        });

        DB::table('customers')->orderBy('id')->each(function (object $customer): void {
            $normalized = $this->normalizePhone((string) $customer->contact_number);

            if ($normalized === $customer->contact_number) {
                return;
            }

            $existing = DB::table('customers')
                ->where('name', $customer->name)
                ->where('contact_number', $normalized)
                ->where('id', '!=', $customer->id)
                ->first();

            if ($existing) {
                $lastOrderAt = collect([$existing->last_order_at, $customer->last_order_at])
                    ->filter()
                    ->sort()
                    ->last();

                DB::table('customers')->where('id', $existing->id)->update([
                    'notes' => $existing->notes ?: $customer->notes,
                    'last_order_at' => $lastOrderAt,
                    'updated_at' => now(),
                ]);
                DB::table('customers')->where('id', $customer->id)->delete();

                return;
            }

            DB::table('customers')->where('id', $customer->id)->update([
                'contact_number' => $normalized,
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Existing values stay in the canonical +63 format on rollback.
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return match (true) {
            str_starts_with($digits, '63') && strlen($digits) === 12 => '+'.$digits,
            str_starts_with($digits, '0') && strlen($digits) === 11 => '+63'.substr($digits, 1),
            str_starts_with($digits, '9') && strlen($digits) === 10 => '+63'.$digits,
            default => $phone,
        };
    }
};
