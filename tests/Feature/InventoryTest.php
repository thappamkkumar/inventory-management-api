<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        Sanctum::actingAs($this->user);
    }

    protected function createInventory(
        int $quantity = 100
    ): Inventory {
        return Inventory::create([
            'product_id' => Product::factory()->create()->id,
            'warehouse_id' => Warehouse::factory()->create()->id,
            'quantity' => $quantity,
        ]);
    }

    #[Test]
    public function authenticated_user_can_list_inventory(): void
    {
        $this->createInventory(100);
        $this->createInventory(50);

        $response = $this->getJson(
            route('inventory.index')
        );

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function authenticated_user_can_view_single_inventory(): void
    {
        $inventory = $this->createInventory(100);

        $response = $this->getJson(
            route('inventory.show', $inventory)
        );

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $inventory->id,
                    'quantity' => 100,
                ],
            ]);
    }

    #[Test]
    public function inventory_can_be_filtered_by_product(): void
    {
        $product = Product::factory()->create();

        $warehouse = Warehouse::factory()->create();

        Inventory::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
        ]);

        $otherInventory = $this->createInventory(50);

        $response = $this->getJson(
            route('inventory.index', [
                'product_id' => $product->id,
            ])
        );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => Inventory::where(
                    'product_id',
                    $product->id
                )->first()->id,
            ])
            ->assertJsonMissing([
                'id' => $otherInventory->id,
            ]);
    }

    #[Test]
    public function inventory_can_be_filtered_by_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();

        $product = Product::factory()->create();

        $inventory = Inventory::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
        ]);

        $otherInventory = $this->createInventory(50);

        $response = $this->getJson(
            route('inventory.index', [
                'warehouse_id' => $warehouse->id,
            ])
        );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $inventory->id,
            ])
            ->assertJsonMissing([
                'id' => $otherInventory->id,
            ]);
    }

    #[Test]
    public function inventory_can_be_filtered_by_minimum_quantity(): void
    {
        $this->createInventory(100);

        $lowInventory = $this->createInventory(10);

        $response = $this->getJson(
            route('inventory.index', [
                'min_quantity' => 50,
            ])
        );

        $response
            ->assertOk()
            ->assertJsonMissing([
                'id' => $lowInventory->id,
            ]);
    }

    #[Test]
    public function inventory_can_be_filtered_by_maximum_quantity(): void
    {
        $highInventory = $this->createInventory(100);

        $this->createInventory(20);

        $response = $this->getJson(
            route('inventory.index', [
                'max_quantity' => 50,
            ])
        );

        $response
            ->assertOk()
            ->assertJsonMissing([
                'id' => $highInventory->id,
            ]);
    }

    #[Test]
    public function inventory_can_be_filtered_by_in_stock_status(): void
    {
        $inStock = $this->createInventory(100);

        $outOfStock = $this->createInventory(0);

        $response = $this->getJson(
            route('inventory.index', [
                'in_stock' => 1,
            ])
        );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $inStock->id,
            ])
            ->assertJsonMissing([
                'id' => $outOfStock->id,
            ]);
    }

    #[Test]
    public function inventory_can_be_sorted_by_quantity(): void
    {
        $low = $this->createInventory(10);
        $high = $this->createInventory(100);

        $response = $this->getJson(
            route('inventory.index', [
                'sort' => 'quantity',
                'direction' => 'desc',
            ])
        );

        $response->assertOk();

        $data = $response->json('data');

        $this->assertSame($high->id, $data[0]['id']);
        $this->assertSame($low->id, $data[1]['id']);
    }

    #[Test]
    public function inventory_can_be_filtered_by_low_stock(): void
    {
        $lowStock = $this->createInventory(5);
        $normalStock = $this->createInventory(50);

        $response = $this->getJson(
            route('inventory.index', [
                'low_stock' => 10,
            ])
        );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $lowStock->id,
            ])
            ->assertJsonMissing([
                'id' => $normalStock->id,
            ]);
    }

    #[Test]
    public function authenticated_user_can_view_inventory_summary(): void
    {
        $this->createInventory(100);
        $this->createInventory(50);
        $this->createInventory(10);
        $this->createInventory(0);

        $response = $this->getJson(
            route('inventory.summary')
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_items' => 4,
                    'total_quantity' => 160,
                    'out_of_stock' => 1,
                    'low_stock' => 1,
                ],
            ]);
    }

}