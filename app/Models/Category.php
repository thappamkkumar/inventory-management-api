<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filters\CategoryFilter;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];    
    

    /**
     * Scope a query to apply filters.
     */
    public function scopeFilter($query, CategoryFilter $filter)
    {
        return $filter->apply($query);
    }
    }
