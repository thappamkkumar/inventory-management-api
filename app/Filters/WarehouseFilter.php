<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class WarehouseFilter
{
    public function __construct(
        protected Request $request
    ) {}

    public function apply(Builder $query): Builder
    {
        $this->search($query);
        $this->status($query);
        $this->sort($query);

        return $query;
    }

    protected function search(Builder $query): void
    {
        if (! $this->request->filled('search')) {
            return;
        }

        $search = $this->request->input('search');

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('state', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%");
        });
    }

    protected function status(Builder $query): void
    {
        if (! $this->request->has('is_active')) {
            return;
        }

        $query->where(
            'is_active',
            filter_var(
                $this->request->input('is_active'),
                FILTER_VALIDATE_BOOLEAN
            )
        );
    }

    protected function sort(Builder $query): void
    {
        $allowedSorts = [
            'name',
            'code',
            'city',
            'created_at',
        ];

        $sort = $this->request->query('sort');

        $direction = strtolower(
            $this->request->query('direction', 'asc')
        );

        if (! in_array($sort, $allowedSorts)) {
            $query->latest();

            return;
        }

        $direction = $direction === 'desc'
            ? 'desc'
            : 'asc';

        $query->orderBy($sort, $direction);
    }
}