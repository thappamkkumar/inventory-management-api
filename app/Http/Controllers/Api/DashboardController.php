<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $inventory = Inventory::query();

        return response()->json([
            'success' => true,
            'data' => [
                'products_count' => Product::count(),
                'warehouses_count' => Warehouse::count(),
                'inventory_items' => (clone $inventory)->count(),
                'total_quantity' => (clone $inventory)->sum('quantity'),
                'low_stock' => (clone $inventory)
                    ->whereBetween('quantity', [1, 10])
                    ->count(),
                'out_of_stock' => (clone $inventory)
                    ->where('quantity', 0)
                    ->count(),
                'recent_transactions' => StockTransaction::with([
                    'inventory.product',
                    'inventory.warehouse',
                ])
                    ->latest()
                    ->limit(5)
                    ->get(),
            ],
        ]);
    }
}