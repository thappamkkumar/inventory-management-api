<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Filters\InventoryFilter;

class InventoryController extends Controller
{
    /**
     * Display inventory records.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $inventory = Inventory::query()
            ->with([
                'product',
                'warehouse',
            ])
            ->filter(new InventoryFilter($request))
            ->paginate($perPage)
            ->withQueryString();

        return InventoryResource::collection($inventory);
    }

    /**
     * Display a single inventory record.
     */
    public function show(Inventory $inventory)
    {
        $inventory->load([
            'product',
            'warehouse',
        ]);

        return new InventoryResource($inventory);
    }


    public function summary()
    {
        $query = Inventory::query();

        $totalItems = (clone $query)->count();

        $totalQuantity = (clone $query)->sum('quantity');

        $outOfStock = (clone $query)
            ->where('quantity', 0)
            ->count();

        $lowStock = (clone $query)
            ->whereBetween('quantity', [1, 10])
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
                'out_of_stock' => $outOfStock,
                'low_stock' => $lowStock,
            ],
        ]);
    }


}