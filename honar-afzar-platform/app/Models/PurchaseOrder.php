<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\BelongsToOrganization;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'uuid',
        'supplier_id',
        'warehouse_id',
        'order_number',
        'order_date',
        'expected_date',
        'received_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'terms',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
        'subtotal' => 'decimal:3',
        'tax_amount' => 'decimal:3',
        'discount_amount' => 'decimal:3',
        'total_amount' => 'decimal:3',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $order) {
            if (empty($order->uuid)) {
                $order->uuid = Str::uuid();
            }
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber($order->organization_id);
            }
        });
    }

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    // Helper Methods
    public static function generateOrderNumber(int $organizationId): string
    {
        $lastOrder = static::where('organization_id', $organizationId)
                          ->latest('id')
                          ->first();
        
        $nextNumber = $lastOrder ? intval(substr($lastOrder->order_number, 4)) + 1 : 1;
        
        return 'PO-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total');
        $this->tax_amount = $this->items->sum(fn($item) => $item->total * ($item->tax_percent / 100));
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->save();
    }

    public function approve(int $userId): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('فقط سفارشات در انتظار قابل تأیید هستند');
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function receive(): void
    {
        if ($this->status !== 'approved') {
            throw new \Exception('فقط سفارشات تأیید شده قابل دریافت هستند');
        }

        $this->update([
            'status' => 'received',
            'received_date' => now(),
        ]);

        // Create stock movements for received items
        foreach ($this->items as $item) {
            StockMovement::create([
                'organization_id' => $this->organization_id,
                'warehouse_id' => $this->warehouse_id,
                'product_id' => $item->product_id,
                'type' => 'receipt',
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_price,
                'reference_type' => PurchaseOrder::class,
                'reference_id' => $this->id,
                'notes' => "دریافت سفارش خرید {$this->order_number}",
                'status' => 'approved',
            ]);
        }
    }

    public function cancel(string $reason = ''): void
    {
        if (in_array($this->status, ['received'])) {
            throw new \Exception('سفارشات دریافت شده قابل لغو نیستند');
        }

        $this->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'پیش‌نویس',
            'pending' => 'در انتظار تأیید',
            'approved' => 'تأیید شده',
            'partial' => 'دریافت جزئی',
            'received' => 'دریافت شده',
            'cancelled' => 'لغو شده',
            default => $this->status,
        };
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->items->isEmpty()) return 0;
        
        $totalOrdered = $this->items->sum('quantity');
        $totalReceived = $this->items->sum('received_quantity');
        
        return $totalOrdered > 0 ? ($totalReceived / $totalOrdered) * 100 : 0;
    }
}
