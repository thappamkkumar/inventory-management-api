<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StockTransactionFilter
{
    public function __construct(
        protected Request $request
    ) {}

    public function apply(Builder $query): Builder
    {
        $this->inventory($query);
        $this->type($query);
        $this->user($query);
        $this->dateRange($query);
        $this->sort($query);

        return $query;
    }

    protected function inventory(Builder $query): void
    {
        if ($this->request->filled('inventory_id')) {
            $query->where(
                'inventory_id',
                $this->request->integer('inventory_id')
            );
        }
    }

    protected function type(Builder $query): void
    {
        if ($this->request->filled('type')) {
            $query->where(
                'type',
                $this->request->input('type')
            );
        }
    }

    protected function user(Builder $query): void
    {
        if ($this->request->filled('user_id')) {
            $query->where(
                'user_id',
                $this->request->integer('user_id')
            );
        }
    }

    protected function dateRange(Builder $query): void
    {
        if ($this->request->filled('from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $this->request->input('from')
            );
        }

        if ($this->request->filled('to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $this->request->input('to')
            );
        }
    }

    protected function sort(Builder $query): void
    {
        $allowedSorts = [
            'created_at',
            'quantity',
            'type',
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

        if (! in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        $direction = $direction === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sort, $direction);
    }
}