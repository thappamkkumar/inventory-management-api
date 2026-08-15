<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'quantity' => fake()->numberBetween(0, 500),
        ];
    }
}