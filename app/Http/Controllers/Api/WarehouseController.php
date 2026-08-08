<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Filters\WarehouseFilter;


class WarehouseController extends Controller
{
    use ApiResponse;

    /**
     * Display a paginated list of warehouses.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $warehouses = Warehouse::query()
            ->filter(new WarehouseFilter($request))
            ->paginate($perPage)
            ->withQueryString();

        return WarehouseResource::collection($warehouses);
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(StoreWarehouseRequest $request)
    {
        $warehouse = Warehouse::create($request->validated());

        return $this->created(
            'Warehouse created successfully.',
            new WarehouseResource($warehouse)
        );
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse)
    {
        return new WarehouseResource($warehouse);
    }

    /**
     * Update the specified warehouse.
     */
    public function update(
        UpdateWarehouseRequest $request,
        Warehouse $warehouse
    ) {
        $warehouse->update($request->validated());

        return $this->success(
            'Warehouse updated successfully.',
            new WarehouseResource($warehouse)
        );
    }

    /**
     * Soft delete the specified warehouse.
     */
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return $this->success(
            'Warehouse deleted successfully.'
        );
    }

    /**
     * Restore a soft deleted warehouse.
     */
    public function restore($id)
    {
        $warehouse = Warehouse::onlyTrashed()->findOrFail($id);

        $warehouse->restore();

        return $this->success(
            'Warehouse restored successfully.',
            new WarehouseResource($warehouse)
        );
    }
}