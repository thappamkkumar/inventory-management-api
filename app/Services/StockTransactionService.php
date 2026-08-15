<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransactionService
{
    public function create(
        Inventory $inventory,
        string $type,
        int $quantity,
        ?string $reference = null,
        ?string $notes = null,
        ?int $userId = null
    ): StockTransaction {
        return DB::transaction(function () use (
            $inventory,
            $type,
            $quantity,
            $reference,
            $notes,
            $userId
        ) {
            $inventory = Inventory::query()
                ->lockForUpdate()
                ->findOrFail($inventory->id);

            $before = $inventory->quantity;

            $after = match ($type) {
                'purchase', 'return' =>
                    $before + $quantity,

                'sale', 'damage' =>
                    $before - $quantity,

                'adjustment' =>
                    $quantity,

                default =>
                    throw ValidationException::withMessages([
                        'type' => 'Invalid stock transaction type.',
                    ]),
            };

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock.',
                ]);
            }

            $transaction = $inventory->transactions()->create([
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference' => $reference,
                'notes' => $notes,
                'user_id' => $userId,
            ]);

            $inventory->update([
                'quantity' => $after,
            ]);

            return $transaction;
        });
    }



    public function transfer(
        Inventory $fromInventory,
        Inventory $toInventory,
        int $quantity,
        ?string $reference = null,
        ?string $notes = null,
        ?int $userId = null
    ): array {
        return DB::transaction(function () use (
            $fromInventory,
            $toInventory,
            $quantity,
            $reference,
            $notes,
            $userId
        ) {
            if ($fromInventory->id === $toInventory->id) {
                throw ValidationException::withMessages([
                    'to_inventory_id' => 'Source and destination inventory must be different.',
                ]);
            }

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Transfer quantity must be greater than zero.',
                ]);
            }

            // Lock both inventory rows.
            $inventoryIds = [
                $fromInventory->id,
                $toInventory->id,
            ];

            sort($inventoryIds);

            Inventory::query()
                ->whereIn('id', $inventoryIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $fromInventory->refresh();
            $toInventory->refresh();

            $fromBefore = $fromInventory->quantity;
            $toBefore = $toInventory->quantity;

            if ($fromBefore < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock in source inventory.',
                ]);
            }

            $fromAfter = $fromBefore - $quantity;
            $toAfter = $toBefore + $quantity;

            $transferOut = $fromInventory->transactions()->create([
                'type' => 'transfer_out',
                'quantity' => $quantity,
                'quantity_before' => $fromBefore,
                'quantity_after' => $fromAfter,
                'reference' => $reference,
                'notes' => $notes,
                'user_id' => $userId,
            ]);

            $transferIn = $toInventory->transactions()->create([
                'type' => 'transfer_in',
                'quantity' => $quantity,
                'quantity_before' => $toBefore,
                'quantity_after' => $toAfter,
                'reference' => $reference,
                'notes' => $notes,
                'user_id' => $userId,
            ]);

            $fromInventory->update([
                'quantity' => $fromAfter,
            ]);

            $toInventory->update([
                'quantity' => $toAfter,
            ]);

            return [
                'transfer_out' => $transferOut,
                'transfer_in' => $transferIn,
            ];
        });
    }

}