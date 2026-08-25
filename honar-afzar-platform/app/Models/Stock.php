<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToOrganization;

class Stock extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'warehouse_id',
        'product_id',
        'batch_number',
        'expiry_date',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'available_quantity' => 'decimal:3',
        'unit_cost' => 'decimal:3',
        'expiry_date' => 'date',
    ];

    // Relationships
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }

    // Scopes
    public function scopeInWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'product.min_stock');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    // Helper Methods
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->product->min_stock;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && 
               $this->expiry_date->isFuture() && 
               $this->expiry_date->diffInDays(now()) <= $days;
    }

    public function getTotalValue(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    public function reserve(float $quantity): void
    {
        if ($this->available_quantity < $quantity) {
            throw new \Exception('موجودی کافی نیست');
        }

        $this->update([
            'reserved_quantity' => $this->reserved_quantity + $quantity,
            'available_quantity' => $this->available_quantity - $quantity,
        ]);
    }

    public function releaseReservation(float $quantity): void
    {
        $this->update([
            'reserved_quantity' => max(0, $this->reserved_quantity - $quantity),
            'available_quantity' => $this->available_quantity + $quantity,
        ]);
    }
}
