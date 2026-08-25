<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'name_fa' => $this->name_fa,
            'code' => $this->code,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'min_stock' => $this->min_stock,
            'max_stock' => $this->max_stock,
            'reorder_point' => $this->reorder_point,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'margin' => $this->margin,
            'has_serial_number' => $this->has_serial_number,
            'has_batch' => $this->has_batch,
            'has_expiry' => $this->has_expiry,
            'is_active' => $this->is_active,
            'image_url' => $this->image_url,
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'unit' => $this->whenLoaded('unit', fn() => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
                'symbol' => $this->unit->symbol,
            ]),
            'stocks_summary' => $this->when($this->relationLoaded('stocks'), fn() => [
                'total_quantity' => $this->stocks->sum('quantity'),
                'total_value' => $this->stocks->sum(fn($s) => $s->quantity * $s->unit_cost),
                'is_low_stock' => $this->isLowStock(),
                'by_warehouse' => $this->stocks->map(fn($stock) => [
                    'warehouse_id' => $stock->warehouse_id,
                    'quantity' => $stock->quantity,
                ]),
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
