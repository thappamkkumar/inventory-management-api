<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filters\WarehouseFilter;
use App\Models\Inventory;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

     protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'contact_person',
        'phone',
        'email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

     
    public function scopeFilter($query, WarehouseFilter $filter)
    {
        return $filter->apply($query);
    }

    /**
     * Get the inventories for the warehouse.
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }


}
