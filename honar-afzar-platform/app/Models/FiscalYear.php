<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToOrganization;

class FiscalYear extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'start_date',
        'end_date',
        'is_current',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    // Relationships
    public function periods()
    {
        return $this->hasMany(AccountingPeriod::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // Scopes
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    // Helper Methods
    public function close(int $userId): void
    {
        if ($this->is_closed) {
            throw new \Exception('سال مالی قبلاً بسته شده است');
        }

        $this->update([
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => $userId,
        ]);

        // Set next year as current
        $nextYear = static::where('organization_id', $this->organization_id)
            ->where('start_date', '>', $this->end_date)
            ->first();
        
        if ($nextYear) {
            $nextYear->update(['is_current' => true]);
        }
    }

    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }
}
