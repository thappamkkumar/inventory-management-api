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

class StockTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        Sanctum::actingAs($this->user);
    }

    protected function createInventory(int $quantity = 100): Inventory
    {
        return Inventory::create([
            'product_id' => Product::factory()->create()->id,
            'warehouse_id' => Warehouse::factory()->create()->id,
            'quantity' => $quantity,
        ]);
    }

    #[Test]
    public function authenticated_user_can_create_purchase_transaction(): void
    {
        $inventory = $this->createInventory(100);

        $response = $this->postJson(
            route('stock-transactions.store'),
            [
                'inventory_id' => $inventory->id,
                'type' => 'purchase',
                'quantity' => 50,
                'reference' => 'PO-001',
                'notes' => 'Purchase stock',
            ]
        );

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Stock transaction created successfully.',
                'data' => [
                    'type' => 'purchase',
                    'quantity' => 50,
                    'quantity_before' => 100,
                    'quantity_after' => 150,
                ],
            ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity' => 150,
        ]);

        $this->assertDatabaseHas('stock_transactions', [
            'inventory_id' => $inventory->id,
            'type' => 'purchase',
            'quantity' => 50,
            'quantity_before' => 100,
            'quantity_after' => 150,
        ]);
    }

    #[Test]
    public function authenticated_user_can_create_sale_transaction(): void
    {
        $inventory = $this->createInventory(100);

        $response = $this->postJson(
            route('stock-transactions.store'),
            [
                'inventory_id' => $inventory->id,
                'type' => 'sale',
                'quantity' => 30,
            ]
        );

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'type' => 'sale',
                    'quantity' => 30,
                    'quantity_before' => 100,
                    'quantity_after' => 70,
                ],
            ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity' => 70,
        ]);
    }

    #[Test]
    public function authenticated_user_can_create_return_transaction(): void
    {
        $inventory = $this->createInventory(100);

        $response = $this->postJson(
            route('stock-transactions.store'),
            [
                'inventory_id' => $inventory->id,
                'type' => 'return',
                'quantity' => 20,
            ]
        );

        $response
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'type' => 'return',
                    'quantity_before' => 100,
                    'quantity_after' => 120,
                ],
            ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity' => 120,
        ]);
    }

    #[Test]
    public function authenticated_user_can_create_damage_transaction(): void
    {
        $inventory = $this->createInventory(100);

        $response = $this->postJson(
            route('stock-transactions.store'),
            [
                'inventory_id' => $inventory->id,
                'type' => 'damage',
                'quantity' => 10,
            ]
        );

        $response
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'type' => 'damage',
                    'quantity_before' => 100,
                    'quantity_after' => 90,
                ],
            ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity' => 90,
        ]);
    }

    #[Test]
    public function authenticated_user_can_create_adjustment_transaction(): void
    {
        $inventory = $this->createInventory(100);

        $response = $this->postJson(
            route('stock-transactions.store'),
            [
                'inventory_id' => $inventory->id,
                'type' => 'adjustment',
                'quantity' => 75,
            ]
        );

        $response
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'type' => 'adjustment',
                    'quantity_before' => 100,
                    'quantity_after' => 75,
                ],
            ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity' => 75,
        ]);
    }

    #[Test]
    public function sale_cannot_create_transaction_with_insufficient_stock(): void
    {
        $inventory = $this->createInventory(10);

        $response = $this->postJson(
            route('stock-transactions.store'),
            [
                'inventory_id' => $inventory->id,
                'type' => 'sale',
                'quantity' => 20,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity' => 10,
        ]);

        $this->assertDatabaseCount('stock_transactions', 0);
    }

    #[Test]
    public function authenticated_user_can_list_stock_transactions(): void
    {
        $inventory = $this->createInventory();

        StockTransaction::factory()
            ->count(5)
            ->create([
                'inventory_id' => $inventory->id,
                'user_id' => $this->user->id,
            ]);

        $response = $this->getJson(
            route('stock-transactions.index')
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
    public function authenticated_user_can_filter_transactions_by_type(): void
    {
        $inventory = $this->createInventory();

        StockTransaction::factory()->create([
            'inventory_id' => $inventory->id,
            'user_id' => $this->user->id,
            'type' => 'purchase',
        ]);

        StockTransaction::factory()->create([
            'inventory_id' => $inventory->id,
            'user_id' => $this->user->id,
            'type' => 'sale',
        ]);

        $response = $this->getJson(
            route('stock-transactions.index', [
                'type' => 'purchase',
            ])
        );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'purchase',
            ])
            ->assertJsonMissing([
                'type' => 'sale',
            ]);
    }

    #[Test]
    public function authenticated_user_can_view_single_stock_transaction(): void
    {
        $inventory = $this->createInventory();

        $transaction = StockTransaction::factory()->create([
            'inventory_id' => $inventory->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson(
            route('stock-transactions.show', $transaction)
        );

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $transaction->id,
                    'inventory_id' => $inventory->id,
                    'type' => $transaction->type,
                ],
            ]);
    }
}