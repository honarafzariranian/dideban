<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\BelongsToOrganization;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'name',
        'code',
        'address',
        'city',
        'phone',
        'manager_id',
        'is_main',
        'status',
        'settings',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'settings' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Warehouse $warehouse) {
            if (empty($warehouse->uuid)) {
                $warehouse->uuid = Str::uuid();
            }
        });
    }

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    // Helper Methods
    public function isMain(): bool
    {
        return $this->is_main;
    }

    public function getStockSummary(): array
    {
        return [
            'total_products' => $this->stocks()->count(),
            'total_value' => $this->stocks()->sum('quantity * unit_cost'),
            'low_stock_count' => $this->stocks()
                ->whereColumn('quantity', '<=', 'product.min_stock')
                ->count(),
        ];
    }
}
