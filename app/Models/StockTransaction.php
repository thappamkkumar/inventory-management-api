<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory;
use App\Models\User;
use App\Filters\StockTransactionFilter;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
        ];
    }

     
    public function scopeFilter(
        $query,
        StockTransactionFilter $filter
    ) {
        return $filter->apply($query);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}