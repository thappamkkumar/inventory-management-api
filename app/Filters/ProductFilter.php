<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductFilter
{
    public function __construct(
        protected Request $request
    ) {}

    public function apply(Builder $query): Builder
    {
        $this->search($query);
        $this->stock($query);
        $this->price($query);
        $this->sort($query);

        return $query;
    }

    protected function search(Builder $query): void
    {
        if (! $this->request->filled('search')) {
            return;
        }

        $search = $this->request->search;

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    protected function stock(Builder $query): void
    {
        match ($this->request->stock) {
            'available' => $query->where('stock', '>', 0),
            'out' => $query->where('stock', 0),
            default => null,
        };
    }

    protected function price(Builder $query): void
    {
        if ($this->request->filled('min_price')) {
            $query->where('price', '>=', $this->request->min_price);
        }

        if ($this->request->filled('max_price')) {
            $query->where('price', '<=', $this->request->max_price);
        }
    }

    protected function sort(Builder $query): void
    {
        $allowed = ['name', 'price', 'stock', 'created_at'];

        $sort = $this->request->query('sort');

        if (! in_array($sort, $allowed)) {
            $query->latest();
            return;
        }

        $direction = strtolower(
            $this->request->query('direction', 'asc')
        ) === 'desc'
            ? 'desc'
            : 'asc';

        $query->orderBy($sort, $direction);
    }
}