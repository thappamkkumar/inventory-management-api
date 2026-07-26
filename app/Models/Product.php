<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
        'stock',
    ];

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

    public function scopeSort($query, ?string $sort, string $direction = 'asc')
    {
        $allowedSorts = ['name', 'price', 'stock', 'created_at'];

        if (! in_array($sort, $allowedSorts)) {
            return $query->latest();
        }

        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sort, $direction);
    }

    public function scopeFilter($query, array $filters)
    {
        // Stock
        if (($filters['stock'] ?? null) === 'available') {
            $query->where('stock', '>', 0);
        }

        if (($filters['stock'] ?? null) === 'out') {
            $query->where('stock', 0);
        }

        // Minimum price
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        // Maximum price
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query;
    }


}
