<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Filters\CategoryFilter;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Display a paginated list of categories.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $categories = Category::query()
        ->filter(new CategoryFilter($request))
        ->paginate($perPage)
        ->withQueryString();

        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return $this->created(
            'Category created successfully.',
            new CategoryResource($category)
        );
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return $this->success(
            'Category updated successfully.',
            new CategoryResource($category)
        );
    }

    /**
     * Soft delete the specified category.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return $this->success(
            'Category deleted successfully.'
        );
    }

    /**
     * Restore a soft deleted category.
     */
    public function restore($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);

        $category->restore();

        return $this->success(
            'Category restored successfully.',
            new CategoryResource($category)
        );
    }
}