<?php

namespace App\Domains\Order\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_amount' => (float) $this->total_amount,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
            // Loading relations safely if they are loaded
            'invoice' => $this->whenLoaded('invoice')
        ];
    }
}