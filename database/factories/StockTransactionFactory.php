<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockTransactionFactory extends Factory
{
    protected $model = StockTransaction::class;

    public function definition(): array
    {
        $before = fake()->numberBetween(10, 100);
        $quantity = fake()->numberBetween(1, $before);

        return [
            'inventory_id' => Inventory::factory(),
            'type' => fake()->randomElement([
                'purchase',
                'sale',
                'adjustment',
                'damage',
                'return',
            ]),
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $before,
            'reference' => fake()->optional()->bothify('REF-####'),
            'notes' => fake()->optional()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}