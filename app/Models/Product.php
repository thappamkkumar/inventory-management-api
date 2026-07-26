<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filters\ProductFilter;
use App\Models\Category;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'description',
        'price',
        'stock',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    public function attributes(): array
    {
        return [
            'sku' => 'SKU',
        ];
    }

    /**
     * Get the category that owns the product.
     */
    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to sort products.
     */

    public function scopeSort($query, ?string $sort, string $direction = 'asc')
    {
        $allowedSorts = ['name', 'price', 'stock', 'created_at'];

        if (! in_array($sort, $allowedSorts)) {
            return $query->latest();
        }

        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sort, $direction);
    }

    /**
     * Scope a query to apply filters.
     */
    public function scopeFilter($query, ProductFilter $filter)
    {
        return $filter->apply($query);
       
    }

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
