<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\BelongsToOrganization;

class Payroll extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'uuid', 'payroll_number', 'title', 'month', 'year',
        'pay_date', 'total_base_salary', 'total_allowances', 'total_deductions',
        'total_insurance', 'total_tax', 'total_net_pay', 'employee_count',
        'status', 'notes', 'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'pay_date' => 'date', 'total_base_salary' => 'decimal:3',
        'total_allowances' => 'decimal:3', 'total_deductions' => 'decimal:3',
        'total_insurance' => 'decimal:3', 'total_tax' => 'decimal:3',
        'total_net_pay' => 'decimal:3', 'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payroll $p) {
            $p->uuid ??= Str::uuid();
            $p->payroll_number ??= static::generateNumber($p->organization_id);
        });
    }

    public function items() { return $this->hasMany(PayrollItem::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    public static function generateNumber(int $orgId): string
    {
        $last = static::where('organization_id', $orgId)->latest('id')->first();
        $n = $last ? intval(substr($last->payroll_number, 3)) + 1 : 1;
        return 'PR-' . str_pad($n, 6, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(): void
    {
        $this->total_base_salary = $this->items->sum('base_salary');
        $this->total_allowances = $this->items->sum('allowances');
        $this->total_deductions = $this->items->sum(fn($i) => $i->leave_deduction + $i->other_deductions);
        $this->total_insurance = $this->items->sum('insurance_ee');
        $this->total_tax = $this->items->sum('tax');
        $this->total_net_pay = $this->items->sum('net_pay');
        $this->employee_count = $this->items->count();
        $this->save();
    }

    public function approve(int $userId): void
    {
        if ($this->status !== 'pending') throw new \Exception('فقط لیست‌های در انتظار قابل تأیید هستند');
        $this->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'پیش‌نویس', 'pending' => 'در انتظار', 'approved' => 'تأیید شده',
            'paid' => 'پرداخت شده', 'cancelled' => 'لغو شده', default => $this->status,
        };
    }
}
