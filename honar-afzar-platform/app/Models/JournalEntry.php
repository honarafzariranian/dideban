<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Traits\BelongsToOrganization;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'uuid',
        'fiscal_year_id',
        'accounting_period_id',
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'type',
        'description',
        'total_debit',
        'total_credit',
        'status',
        'is_balanced',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:3',
        'total_credit' => 'decimal:3',
        'is_balanced' => 'boolean',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (JournalEntry $entry) {
            if (empty($entry->uuid)) {
                $entry->uuid = \Illuminate\Support\Str::uuid();
            }
            if (empty($entry->entry_number)) {
                $entry->entry_number = static::generateEntryNumber($entry->organization_id);
            }
        });
    }

    // Relationships
    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function accountingPeriod()
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reference()
    {
        return $this->morphTo();
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

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    // Helper Methods
    public static function generateEntryNumber(int $organizationId): string
    {
        $lastEntry = static::where('organization_id', $organizationId)
                          ->latest('id')
                          ->first();
        
        $nextNumber = $lastEntry ? intval(substr($lastEntry->entry_number, 3)) + 1 : 1;
        
        return 'JE-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(): void
    {
        $this->total_debit = $this->lines->sum('debit');
        $this->total_credit = $this->lines->sum('credit');
        $this->is_balanced = abs($this->total_debit - $this->total_credit) < 0.01;
        $this->save();
    }

    public function approve(int $userId): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('فقط اسناد در انتظار قابل تأیید هستند');
        }

        if (!$this->is_balanced) {
            throw new \Exception('سند موازنه نیست');
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function post(int $userId): void
    {
        if ($this->status !== 'approved') {
            throw new \Exception('فقط اسناد تأیید شده قابل ثبت هستند');
        }

        $this->update([
            'status' => 'posted',
            'posted_by' => $userId,
            'posted_at' => now(),
        ]);
    }

    public function reverse(int $userId, string $reason = ''): void
    {
        if ($this->status !== 'posted') {
            throw new \Exception('فقط اسناد ثبت شده قابل برگشت هستند');
        }

        // Create reverse entry
        $reverseEntry = static::create([
            'organization_id' => $this->organization_id,
            'fiscal_year_id' => $this->fiscal_year_id,
            'accounting_period_id' => $this->accounting_period_id,
            'entry_date' => now(),
            'type' => 'general',
            'description' => "برگشت سند {$this->entry_number}: {$reason}",
            'status' => 'draft',
            'created_by' => $userId,
        ]);

        foreach ($this->lines as $line) {
            $reverseEntry->lines()->create([
                'account_id' => $line->account_id,
                'debit' => $line->credit,
                'credit' => $line->debit,
                'description' => "برگشت: {$line->description}",
            ]);
        }

        $reverseEntry->calculateTotals();
        $reverseEntry->update(['status' => 'pending']);

        $this->update(['status' => 'reversed']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'پیش‌نویس',
            'pending' => 'در انتظار تأیید',
            'approved' => 'تأیید شده',
            'posted' => 'ثبت شده',
            'rejected' => 'رد شده',
            'reversed' => 'برگشت خورده',
            default => $this->status,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'general' => 'سند عمومی',
            'receipt' => 'سند دریافت',
            'payment' => 'سند پرداخت',
            'journal' => 'سند روزنامه',
            'opening' => 'سند افتتاحیه',
            'closing' => 'سند اختتامیه',
            default => $this->type,
        };
    }
}
