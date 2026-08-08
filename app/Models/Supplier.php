<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filters\SupplierFilter;
use App\Models\Product;

class Supplier extends Model
{
    //
     use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'contact_person',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'is_active',
    ];

     protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to filter suppliers.
     */
    public function scopeFilter($query, SupplierFilter $filter)
    {
        return $filter->apply($query);
    }

    /**
     * Get the products for the supplier.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
