<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display inventory records.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $inventory = Inventory::with([
            'product',
            'warehouse',
        ])
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
}