<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToOrganization;

class ChartOfAccount extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'organization_id',
        'parent_id',
        'code',
        'name',
        'name_fa',
        'type',
        'subtype',
        'description',
        'is_group',
        'is_leaf',
        'is_active',
        'opening_balance',
        'currency',
        'metadata',
    ];

    protected $casts = [
        'is_group' => 'boolean',
        'is_leaf' => 'boolean',
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:3',
        'metadata' => 'array',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAssets($query)
    {
        return $query->where('type', 'asset');
    }

    public function scopeLiabilities($query)
    {
        return $query->where('type', 'liability');
    }

    public function scopeEquity($query)
    {
        return $query->where('type', 'equity');
    }

    public function scopeRevenue($query)
    {
        return $query->where('type', 'revenue');
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeGroups($query)
    {
        return $query->where('is_group', true);
    }

    public function scopeLeaves($query)
    {
        return $query->where('is_leaf', true);
    }

    // Helper Methods
    public function isGroup(): bool
    {
        return $this->is_group;
    }

    public function isLeaf(): bool
    {
        return $this->is_leaf;
    }

    public function getBalance(): float
    {
        $debit = $this->journalEntryLines()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('debit');
        
        $credit = $this->journalEntryLines()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->sum('credit');

        return match($this->type) {
            'asset', 'expense' => $debit - $credit + $this->opening_balance,
            'liability', 'equity', 'revenue' => $credit - $debit + $this->opening_balance,
            default => 0,
        };
    }

    public function getChildrenBalance(): float
    {
        $balance = $this->getBalance();
        
        foreach ($this->children as $child) {
            $balance += $child->getChildrenBalance();
        }
        
        return $balance;
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'asset' => 'دارایی',
            'liability' => 'بدهی',
            'equity' => 'سرمایه',
            'revenue' => 'درآمد',
            'expense' => 'هزینه',
            default => $this->type,
        };
    }

    public function getFullCodeAttribute(): string
    {
        $parts = [];
        $current = $this;
        
        while ($current) {
            $parts[] = $current->code;
            $current = $current->parent;
        }
        
        return implode('.', array_reverse($parts));
    }
}
