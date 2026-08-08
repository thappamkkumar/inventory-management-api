<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,

            'quantity' => $this->quantity,

            'product' => ProductResource::make(
                $this->whenLoaded('product')
            ),

            'warehouse' => WarehouseResource::make(
                $this->whenLoaded('warehouse')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}