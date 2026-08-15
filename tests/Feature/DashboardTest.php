<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(
            User::factory()->create()
        );
    }

    #[Test]
    public function authenticated_user_can_view_dashboard(): void
    {
        $product = Product::factory()->create();

        $warehouse = Warehouse::factory()->create();

        $inventory = Inventory::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
        ]);

        StockTransaction::factory()->create([
            'inventory_id' => $inventory->id,
        ]);

        $response = $this->getJson(
            route('dashboard.index')
        );

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'products_count',
                    'warehouses_count',
                    'inventory_items',
                    'total_quantity',
                    'low_stock',
                    'out_of_stock',
                    'recent_transactions',
                ],
            ]);
    }
}