<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $package = [
            'name' => 'The Bellyssimo Barkadahan',
            'weight' => '3 kg',
            'serves' => '8–10 pax',
            'price' => 2000,
            'image' => 'images/lechon-barkadahan-3kg.png',
        ];

        return [
            'reference' => 'TB-'.fake()->unique()->numerify('########'),
            'items' => [[
                'package_key' => 'barkadahan',
                'package' => $package,
                'flavor' => 'Regular',
                'spicy_upgrade' => 0,
                'line_total' => 2000,
            ]],
            'quantity' => 1,
            'package_subtotal' => 2000,
            'spicy_subtotal' => 0,
            'order_total' => 2000,
            'fulfillment' => 'Pickup',
            'delivery_location' => null,
            'customer_name' => fake()->name(),
            'contact_number' => '+63'.fake()->numerify('9#########'),
            'email' => null,
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '12:00',
            'notes' => fake()->optional()->sentence(),
            'status' => 'Pending confirmation',
            'payment_status' => 'Unpaid',
            'payment_method' => 'Cash',
            'submitted_at' => now(),
            'completed_at' => null,
        ];
    }
}
