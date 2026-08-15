<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class InventoryFilter
{
    public function __construct(
        protected Request $request
    ) {}

    public function apply(Builder $query): Builder
    {
        $this->product($query);
        $this->warehouse($query);
        $this->quantity($query);
        $this->stockStatus($query);
        $this->lowStock($query);
        $this->sort($query);

        return $query;
    }

    protected function product(Builder $query): void
    {
        if ($this->request->filled('product_id')) {
            $query->where(
                'product_id',
                $this->request->integer('product_id')
            );
        }
    }

    protected function warehouse(Builder $query): void
    {
        if ($this->request->filled('warehouse_id')) {
            $query->where(
                'warehouse_id',
                $this->request->integer('warehouse_id')
            );
        }
    }

    protected function quantity(Builder $query): void
    {
        if ($this->request->filled('min_quantity')) {
            $query->where(
                'quantity',
                '>=',
                $this->request->integer('min_quantity')
            );
        }

        if ($this->request->filled('max_quantity')) {
            $query->where(
                'quantity',
                '<=',
                $this->request->integer('max_quantity')
            );
        }
    }

    protected function stockStatus(Builder $query): void
    {
        if (! $this->request->filled('in_stock')) {
            return;
        }

        if ($this->request->boolean('in_stock')) {
            $query->where('quantity', '>', 0);
        } else {
            $query->where('quantity', '=', 0);
        }
    }

    protected function lowStock(Builder $query): void
    {
        if ($this->request->filled('low_stock')) {
            $query->where(
                'quantity',
                '<=',
                $this->request->integer('low_stock')
            );
        }
    }

    protected function sort(Builder $query): void
    {
        $allowedSorts = [
            'quantity',
            'created_at',
            'updated_at',
        ];

        $sort = $this->request->query(
            'sort',
            'created_at'
        );

        $direction = strtolower(
            $this->request->query(
                'direction',
                'desc'
            )
        );

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $direction = $direction === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sort, $direction);
    }
}