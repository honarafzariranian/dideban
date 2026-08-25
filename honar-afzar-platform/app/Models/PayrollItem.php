<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToOrganization;

class PayrollItem extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'payroll_id', 'employee_id', 'base_salary', 'allowances',
        'overtime_hours', 'overtime_amount', 'bonus', 'leave_deduction',
        'insurance_ee', 'insurance_er', 'tax', 'other_deductions',
        'gross_pay', 'net_pay', 'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:3', 'allowances' => 'decimal:3',
        'overtime_hours' => 'decimal:2', 'overtime_amount' => 'decimal:3',
        'bonus' => 'decimal:3', 'leave_deduction' => 'decimal:3',
        'insurance_ee' => 'decimal:3', 'insurance_er' => 'decimal:3',
        'tax' => 'decimal:3', 'other_deductions' => 'decimal:3',
        'gross_pay' => 'decimal:3', 'net_pay' => 'decimal:3',
    ];

    public function payroll() { return $this->belongsTo(Payroll::class); }
    public function employee() { return $this->belongsTo(Employee::class); }

    public function calculate(): void
    {
        $this->gross_pay = $this->base_salary + $this->allowances + $this->overtime_amount + $this->bonus;
        $this->net_pay = $this->gross_pay - $this->leave_deduction - $this->insurance_ee - $this->tax - $this->other_deductions;
    }
}
