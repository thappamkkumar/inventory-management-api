<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CategoryFilter
{
    public function __construct(protected Request $request)
    {
    }

    public function apply(Builder $query): Builder
    {
        $this->search($query);
        $this->sort($query);

        return $query;
    }

    protected function search(Builder $query): void
    {
        if ($search = $this->request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
    }

    protected function sort(Builder $query): void
    {
        $sortBy = $this->request->input('sort_by', 'created_at');
        $sortOrder = $this->request->input('sort_order', 'desc');

        $allowed = [
            'name',
            'slug',
            'created_at',
        ];

        if (! in_array($sortBy, $allowed)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortOrder);
    }
}