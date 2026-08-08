<?php

namespace App\Http\Controllers\Api;

use App\Filters\SupplierFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ApiResponse;

    /**
     * Display a paginated list of suppliers.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $suppliers = Supplier::query()
            ->filter(new SupplierFilter($request))
            ->paginate($perPage)
            ->withQueryString();

        return SupplierResource::collection($suppliers);
    }

    /**
     * Store a newly created supplier.
     */
    public function store(StoreSupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return $this->created(
            'Supplier created successfully.',
            new SupplierResource($supplier)
        );
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier)
    {
        return new SupplierResource($supplier);
    }

    /**
     * Update the specified supplier.
     */
    public function update(
        UpdateSupplierRequest $request,
        Supplier $supplier
    ) {
        $supplier->update($request->validated());

        return $this->success(
            'Supplier updated successfully.',
            new SupplierResource($supplier)
        );
    }

    /**
     * Soft delete the specified supplier.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return $this->success(
            'Supplier deleted successfully.'
        );
    }

    /**
     * Restore a soft deleted supplier.
     */
    public function restore($id)
    {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);

        $supplier->restore();

        return $this->success(
            'Supplier restored successfully.',
            new SupplierResource($supplier)
        );
    }
}