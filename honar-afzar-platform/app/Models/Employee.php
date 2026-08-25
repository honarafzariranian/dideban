<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\BelongsToOrganization;

class Employee extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'uuid', 'department_id', 'employee_number',
        'first_name', 'last_name', 'national_id', 'phone', 'email',
        'birth_date', 'gender', 'marital_status', 'address',
        'insurance_number', 'bank_account_number', 'hire_date',
        'termination_date', 'status', 'user_id', 'metadata',
    ];

    protected $casts = [
        'birth_date' => 'date', 'hire_date' => 'date', 'termination_date' => 'date',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(fn(Employee $e) => $e->uuid ??= Str::uuid());
    }

    public function department() { return $this->belongsTo(Department::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function salaryStructure() { return $this->hasOne(SalaryStructure::class)->where('is_active', true); }
    public function payrollItems() { return $this->hasMany(PayrollItem::class); }

    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeForDepartment($q, int $deptId) { return $q->where('department_id', $deptId); }

    public function getFullNameAttribute(): string { return "{$this->first_name} {$this->last_name}"; }
    public function isActive(): bool { return $this->status === 'active'; }
    public function getTenureAttribute(): int { return $this->hire_date->diffInDays(now()); }
}
