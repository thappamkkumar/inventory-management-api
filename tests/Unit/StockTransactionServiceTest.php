<?php

namespace Tests\Unit;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\StockTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockTransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockTransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(StockTransactionService::class);
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
    public function purchase_increases_inventory(): void
    {
        $inventory = $this->createInventory(100);
        $user = User::factory()->create();

        $transaction = $this->service->create(
            $inventory,
            'purchase',
            50,
            'PO-001',
            'New stock',
            $user->id
        );

        $this->assertSame(150, $inventory->fresh()->quantity);

        $this->assertSame(100, $transaction->quantity_before);
        $this->assertSame(150, $transaction->quantity_after);
        $this->assertSame('purchase', $transaction->type);
        $this->assertSame(50, $transaction->quantity);
        $this->assertSame($user->id, $transaction->user_id);

        $this->assertDatabaseHas('stock_transactions', [
            'inventory_id' => $inventory->id,
            'type' => 'purchase',
            'quantity' => 50,
            'quantity_before' => 100,
            'quantity_after' => 150,
        ]);
    }

    #[Test]
    public function sale_decreases_inventory(): void
    {
        $inventory = $this->createInventory(100);

        $transaction = $this->service->create(
            $inventory,
            'sale',
            30
        );

        $this->assertSame(70, $inventory->fresh()->quantity);

        $this->assertSame(100, $transaction->quantity_before);
        $this->assertSame(70, $transaction->quantity_after);
    }

    #[Test]
    public function return_increases_inventory(): void
    {
        $inventory = $this->createInventory(100);

        $transaction = $this->service->create(
            $inventory,
            'return',
            20
        );

        $this->assertSame(120, $inventory->fresh()->quantity);

        $this->assertSame(100, $transaction->quantity_before);
        $this->assertSame(120, $transaction->quantity_after);
    }

    #[Test]
    public function damage_decreases_inventory(): void
    {
        $inventory = $this->createInventory(100);

        $transaction = $this->service->create(
            $inventory,
            'damage',
            10
        );

        $this->assertSame(90, $inventory->fresh()->quantity);

        $this->assertSame(100, $transaction->quantity_before);
        $this->assertSame(90, $transaction->quantity_after);
    }

    #[Test]
    public function adjustment_sets_inventory_to_given_quantity(): void
    {
        $inventory = $this->createInventory(100);

        $transaction = $this->service->create(
            $inventory,
            'adjustment',
            75
        );

        $this->assertSame(75, $inventory->fresh()->quantity);

        $this->assertSame(100, $transaction->quantity_before);
        $this->assertSame(75, $transaction->quantity_after);
    }

    #[Test]
    public function sale_cannot_reduce_inventory_below_zero(): void
    {
        $inventory = $this->createInventory(10);

        $this->expectException(ValidationException::class);

        $this->service->create(
            $inventory,
            'sale',
            20
        );

        $this->assertSame(10, $inventory->fresh()->quantity);

        $this->assertDatabaseCount('stock_transactions', 0);
    }

    #[Test]
    public function damage_cannot_reduce_inventory_below_zero(): void
    {
        $inventory = $this->createInventory(5);

        $this->expectException(ValidationException::class);

        $this->service->create(
            $inventory,
            'damage',
            10
        );

        $this->assertSame(5, $inventory->fresh()->quantity);

        $this->assertDatabaseCount('stock_transactions', 0);
    }

    #[Test]
    public function invalid_transaction_type_is_rejected(): void
    {
        $inventory = $this->createInventory(100);

        $this->expectException(ValidationException::class);

        $this->service->create(
            $inventory,
            'invalid',
            10
        );

        $this->assertSame(100, $inventory->fresh()->quantity);

        $this->assertDatabaseCount('stock_transactions', 0);
    }


    #[Test]
    public function stock_can_be_transferred_between_inventories(): void
    {
        $fromInventory = $this->createInventory(100);
        $toInventory = $this->createInventory(20);

        $result = $this->service->transfer(
            $fromInventory,
            $toInventory,
            30,
            'TR-001',
            'Warehouse transfer',
            User::factory()->create()->id
        );

        $this->assertSame(70, $fromInventory->fresh()->quantity);
        $this->assertSame(50, $toInventory->fresh()->quantity);

        $this->assertSame(
            'transfer_out',
            $result['transfer_out']->type
        );

        $this->assertSame(
            'transfer_in',
            $result['transfer_in']->type
        );

        $this->assertSame(100, $result['transfer_out']->quantity_before);
        $this->assertSame(70, $result['transfer_out']->quantity_after);

        $this->assertSame(20, $result['transfer_in']->quantity_before);
        $this->assertSame(50, $result['transfer_in']->quantity_after);

        $this->assertDatabaseCount('stock_transactions', 2);
    }


    #[Test]
    public function transfer_cannot_exceed_source_inventory(): void
    {
        $fromInventory = $this->createInventory(10);
        $toInventory = $this->createInventory(20);

        $this->expectException(ValidationException::class);

        $this->service->transfer(
            $fromInventory,
            $toInventory,
            50
        );

        $this->assertSame(10, $fromInventory->fresh()->quantity);
        $this->assertSame(20, $toInventory->fresh()->quantity);

        $this->assertDatabaseCount('stock_transactions', 0);
    }


    #[Test]
    public function transfer_cannot_use_same_inventory(): void
    {
        $inventory = $this->createInventory(100);

        $this->expectException(ValidationException::class);

        $this->service->transfer(
            $inventory,
            $inventory,
            20
        );

        $this->assertSame(100, $inventory->fresh()->quantity);

        $this->assertDatabaseCount('stock_transactions', 0);
    }

    #[Test]
    public function transfer_quantity_must_be_greater_than_zero(): void
    {
        $fromInventory = $this->createInventory(100);
        $toInventory = $this->createInventory(20);

        $this->expectException(ValidationException::class);

        $this->service->transfer(
            $fromInventory,
            $toInventory,
            0
        );

        $this->assertSame(100, $fromInventory->fresh()->quantity);
        $this->assertSame(20, $toInventory->fresh()->quantity);

        $this->assertDatabaseCount('stock_transactions', 0);
    }



}