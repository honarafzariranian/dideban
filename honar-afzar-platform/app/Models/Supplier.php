<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\BelongsToOrganization;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'postal_code',
        'national_id',
        'registration_number',
        'tax_number',
        'credit_limit',
        'current_balance',
        'payment_terms_days',
        'notes',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:3',
        'current_balance' => 'decimal:3',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Supplier $supplier) {
            if (empty($supplier->uuid)) {
                $supplier->uuid = Str::uuid();
            }
        });
    }

    // Relationships
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function products()
    {
        return $this->belongsToMany(InventoryProduct::class, 'supplier_products')
                    ->withPivot(['price', 'lead_time_days', 'is_preferred'])
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePreferred($query)
    {
        return $query->whereHas('products', function ($q) {
            $q->wherePivot('is_preferred', true);
        });
    }

    // Helper Methods
    public function getBalanceAttribute(): float
    {
        return $this->current_balance;
    }

    public function getCreditAvailableAttribute(): float
    {
        return $this->credit_limit - $this->current_balance;
    }

    public function hasSufficientCredit(float $amount): bool
    {
        return $this->credit_available >= $amount;
    }

    public function getPerformanceMetrics(): array
    {
        $orders = $this->purchaseOrders()->where('status', 'received')->get();
        
        return [
            'total_orders' => $orders->count(),
            'total_value' => $orders->sum('total_amount'),
            'average_order_value' => $orders->avg('total_amount'),
            'on_time_delivery_rate' => $this->calculateOnTimeDeliveryRate($orders),
            'quality_score' => $this->calculateQualityScore($orders),
        ];
    }

    protected function calculateOnTimeDeliveryRate($orders): float
    {
        if ($orders->isEmpty()) return 0;
        
        $onTime = $orders->filter(fn($order) => 
            $order->received_date <= $order->expected_date
        )->count();
        
        return ($onTime / $orders->count()) * 100;
    }

    protected function calculateQualityScore($orders): float
    {
        // Simplified quality score based on return rate
        if ($orders->isEmpty()) return 100;
        
        $totalItems = $orders->sum(fn($o) => $o->items->sum('quantity'));
        $returnedItems = $orders->sum(fn($o) => $o->items->sum('returned_quantity'));
        
        if ($totalItems == 0) return 100;
        
        return (($totalItems - $returnedItems) / $totalItems) * 100;
    }
}
