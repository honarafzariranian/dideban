<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\BelongsToOrganization;

class Payment extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'uuid',
        'type',
        'payment_number',
        'payment_date',
        'payee_type',
        'payee_id',
        'payee_name',
        'bank_account_id',
        'amount',
        'payment_method',
        'reference_number',
        'check_number',
        'check_date',
        'tracking_number',
        'description',
        'status',
        'invoice_id',
        'journal_entry_id',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:3',
        'check_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->uuid)) {
                $payment->uuid = Str::uuid();
            }
            if (empty($payment->payment_number)) {
                $payment->payment_number = static::generatePaymentNumber($payment->organization_id, $payment->type);
            }
        });
    }

    // Relationships
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function journalEntry()
    {
        return $this->hasOne(JournalEntry::class, 'reference_id')
                    ->where('reference_type', Payment::class);
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
    public function scopeReceipts($query)
    {
        return $query->where('type', 'receipt');
    }

    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    // Helper Methods
    public static function generatePaymentNumber(int $organizationId, string $type): string
    {
        $prefix = $type === 'receipt' ? 'RC' : 'PM';
        
        $lastPayment = static::where('organization_id', $organizationId)
                            ->where('type', $type)
                            ->latest('id')
                            ->first();
        
        $nextNumber = $lastPayment ? intval(substr($lastPayment->payment_number, 3)) + 1 : 1;
        
        return $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function approve(int $userId): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('فقط پرداخت‌های در انتظار قابل تأیید هستند');
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        // Update bank account balance
        if ($this->bank_account_id) {
            $bankAccount = BankAccount::find($this->bank_account_id);
            
            if ($this->type === 'receipt') {
                $bankAccount->current_balance += $this->amount;
            } else {
                $bankAccount->current_balance -= $this->amount;
            }
            
            $bankAccount->save();
        }

        // Record payment against invoice
        if ($this->invoice_id) {
            $invoice = Invoice::find($this->invoice_id);
            $invoice->recordPayment($this->amount, $this->id);
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'پیش‌نویس',
            'pending' => 'در انتظار تأیید',
            'approved' => 'تأیید شده',
            'completed' => 'تکمیل شده',
            'bounced' => 'برگشتی',
            'cancelled' => 'لغو شده',
            default => $this->status,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'receipt' => 'دریافت',
            'payment' => 'پرداخت',
            default => $this->type,
        };
    }

    public function getMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'نقد',
            'check' => 'چک',
            'bank_transfer' => 'انتقال بانکی',
            'card' => 'کارت',
            default => $this->payment_method,
        };
    }
}
