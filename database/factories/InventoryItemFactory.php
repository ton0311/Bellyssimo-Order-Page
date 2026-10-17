<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'category' => fake()->randomElement(['Ingredient', 'Packaging', 'Kitchen supply']),
            'quantity' => fake()->randomFloat(2, 0, 50),
            'unit' => fake()->randomElement(['kg', 'piece', 'bottle']),
            'low_stock_threshold' => fake()->randomFloat(2, 1, 10),
            'unit_cost' => fake()->optional()->randomFloat(2, 10, 500),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
