<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\BelongsToOrganization;

class InventoryProduct extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $table = 'inventory_products';

    protected $fillable = [
        'organization_id',
        'category_id',
        'unit_id',
        'name',
        'name_fa',
        'code',
        'sku',
        'barcode',
        'qr_code',
        'description',
        'min_stock',
        'max_stock',
        'reorder_point',
        'cost_price',
        'selling_price',
        'image_path',
        'has_serial_number',
        'has_batch',
        'has_expiry',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'min_stock' => 'decimal:3',
        'max_stock' => 'decimal:3',
        'reorder_point' => 'decimal:3',
        'cost_price' => 'decimal:3',
        'selling_price' => 'decimal:3',
        'has_serial_number' => 'boolean',
        'has_batch' => 'boolean',
        'has_expiry' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (InventoryProduct $product) {
            if (empty($product->uuid)) {
                $product->uuid = Str::uuid();
            }
        });
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'product_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'product_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('reorder_point', '>', 'stocks.quantity');
    }

    // Accessors
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    // Helper Methods
    public function getTotalStock(): float
    {
        return $this->stocks()->sum('quantity');
    }

    public function getStockInWarehouse(int $warehouseId): float
    {
        return $this->stocks()
            ->where('warehouse_id', $warehouseId)
            ->sum('quantity');
    }

    public function getTotalValue(): float
    {
        return $this->stocks()->sum('quantity * unit_cost');
    }

    public function isLowStock(): bool
    {
        return $this->getTotalStock() <= $this->reorder_point;
    }

    public function getMarginAttribute(): float
    {
        if ($this->cost_price == 0) return 0;
        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }
}
