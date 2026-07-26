<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\ApiResponse;
use App\Filters\ProductFilter;

class ProductController extends Controller
{
    use ApiResponse;
   
    /**
    * Display a paginated list of products.
    */
    public function index(Request $request)
    {
         
        $perPage = $request->integer('per_page', 10);
 

        $products = Product::with('category')  
            ->filter(new ProductFilter($request)) 
            ->paginate($perPage);

        return ProductResource::collection($products);

    }

    /**
     *  Create a new product..
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());
        $product->load('category');
        return $this->created(
            'Product created successfully.',
            new ProductResource($product)
        );
        
    }

    /**
     * Display a single product.
     */
    public function show(Product $product)
    {
        $product->load('category');
        return new ProductResource($product);
    }

    /**
     * Update an existing product.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        $product->load('category');
        return $this->success(
            'Product updated successfully.',
            new ProductResource($product)
        );
    }

    /**
     * Soft delete a product.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return $this->success(
            'Product deleted successfully.'
        );
    }

    /**
     * Restore a soft deleted product.
     */
    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        $product->restore();
        
        $product->load('category');
        return $this->success(
            'Product restored successfully.',
            new ProductResource($product)
        );
    }
}