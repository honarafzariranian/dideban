<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\BelongsToOrganization;

class StockMovement extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'uuid',
        'warehouse_id',
        'product_id',
        'type',
        'quantity',
        'unit_cost',
        'total_cost',
        'reference_type',
        'reference_id',
        'batch_number',
        'expiry_date',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:3',
        'total_cost' => 'decimal:3',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (StockMovement $movement) {
            if (empty($movement->uuid)) {
                $movement->uuid = Str::uuid();
            }
            $movement->total_cost = $movement->quantity * $movement->unit_cost;
        });
    }

    // Relationships
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeReceipts($query)
    {
        return $query->where('type', 'receipt');
    }

    public function scopeIssues($query)
    {
        return $query->where('type', 'issue');
    }

    public function scopeTransfers($query)
    {
        return $query->where('type', 'transfer');
    }

    public function scopeAdjustments($query)
    {
        return $query->where('type', 'adjustment');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeInWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Helper Methods
    public function approve(int $userId): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('فقط حرکات در انتظار قابل تأیید هستند');
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        // Update stock
        $this->updateStock();
    }

    public function cancel(int $userId, string $reason = ''): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('فقط حرکات در انتظار قابل لغو هستند');
        }

        $this->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);
    }

    protected function updateStock(): void
    {
        $stock = Stock::firstOrCreate(
            [
                'organization_id' => $this->organization_id,
                'warehouse_id' => $this->warehouse_id,
                'product_id' => $this->product_id,
                'batch_number' => $this->batch_number,
            ],
            [
                'quantity' => 0,
                'unit_cost' => $this->unit_cost,
            ]
        );

        switch ($this->type) {
            case 'receipt':
            case 'return':
                $stock->quantity += $this->quantity;
                break;
            case 'issue':
                $stock->quantity -= $this->quantity;
                break;
            case 'adjustment':
                $stock->quantity += $this->quantity; // Can be negative
                break;
        }

        $stock->available_quantity = $stock->quantity - $stock->reserved_quantity;
        $stock->save();
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'receipt' => 'رسید',
            'issue' => 'حواله',
            'transfer' => 'انتقال',
            'adjustment' => ' تعدیل',
            'return' => 'مرجوعی',
            default => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'در انتظار',
            'approved' => 'تأیید شده',
            'cancelled' => 'لغو شده',
            default => $this->status,
        };
    }
}
