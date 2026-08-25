<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\BelongsToOrganization;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'uuid',
        'type',
        'invoice_number',
        'invoice_date',
        'due_date',
        'reference_type',
        'reference_id',
        'reference_name',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'notes',
        'terms',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:3',
        'tax_amount' => 'decimal:3',
        'discount_amount' => 'decimal:3',
        'total_amount' => 'decimal:3',
        'paid_amount' => 'decimal:3',
        'remaining_amount' => 'decimal:3',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->uuid)) {
                $invoice->uuid = Str::uuid();
            }
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber($invoice->organization_id, $invoice->type);
            }
            $invoice->remaining_amount = $invoice->total_amount - $invoice->paid_amount;
        });
    }

    // Relationships
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function journalEntry()
    {
        return $this->hasOne(JournalEntry::class, 'reference_id')
                    ->where('reference_type', Invoice::class);
    }

    // Scopes
    public function scopeSales($query)
    {
        return $query->where('type', 'sales');
    }

    public function scopePurchases($query)
    {
        return $query->where('type', 'purchase');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', '!=', 'paid');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Helper Methods
    public static function generateInvoiceNumber(int $organizationId, string $type): string
    {
        $prefix = $type === 'sales' ? 'SI' : 'PI';
        
        $lastInvoice = static::where('organization_id', $organizationId)
                            ->where('type', $type)
                            ->latest('id')
                            ->first();
        
        $nextNumber = $lastInvoice ? intval(substr($lastInvoice->invoice_number, 3)) + 1 : 1;
        
        return $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total');
        $this->tax_amount = $this->items->sum(fn($item) => $item->total * ($item->tax_percent / 100));
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->remaining_amount = $this->total_amount - $this->paid_amount;
        $this->save();
    }

    public function recordPayment(float $amount, int $paymentId): void
    {
        $this->paid_amount += $amount;
        $this->remaining_amount = $this->total_amount - $this->paid_amount;
        
        if ($this->remaining_amount <= 0) {
            $this->status = 'paid';
        } else {
            $this->status = 'partial';
        }
        
        $this->save();
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'paid';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'پیش‌نویس',
            'pending' => 'در انتظار تأیید',
            'approved' => 'تأیید شده',
            'paid' => 'پرداخت شده',
            'partial' => 'پرداخت جزئی',
            'overdue' => 'سررسید گذشته',
            'cancelled' => 'لغو شده',
            default => $this->status,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'sales' => 'فاکتور فروش',
            'purchase' => 'فاکتور خرید',
            default => $this->type,
        };
    }

    public function getPaymentPercentageAttribute(): float
    {
        return $this->total_amount > 0 ? ($this->paid_amount / $this->total_amount) * 100 : 0;
    }
}
