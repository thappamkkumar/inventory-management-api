<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'inventory_id' => $this->inventory_id,

            'type' => $this->type,

            'quantity' => $this->quantity,
            'quantity_before' => $this->quantity_before,
            'quantity_after' => $this->quantity_after,

            'reference' => $this->reference,
            'notes' => $this->notes,

            'user_id' => $this->user_id,

            'inventory' => InventoryResource::make(
                $this->whenLoaded('inventory')
            ),

            'user' => UserResource::make(
                $this->whenLoaded('user')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}