<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToOrganization;

class SalaryStructure extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'employee_id', 'base_salary', 'allowances',
        'deductions', 'insurance_share', 'tax_rate', 'effective_date',
        'expiry_date', 'is_active', 'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:3', 'allowances' => 'decimal:3',
        'deductions' => 'decimal:3', 'insurance_share' => 'decimal:3',
        'tax_rate' => 'decimal:2', 'effective_date' => 'date',
        'expiry_date' => 'date', 'is_active' => 'boolean',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeCurrent($q) { return $q->where('effective_date', '<=', now())->where(function($q) { $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()); }); }

    public function getTotalSalaryAttribute(): float { return $this->base_salary + $this->allowances - $this->deductions; }
}
