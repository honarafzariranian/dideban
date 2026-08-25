<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\BelongsToOrganization;

class Opportunity extends Model {
    use HasFactory, SoftDeletes, BelongsToOrganization;
    protected $fillable = [
        'organization_id', 'uuid', 'customer_id', 'title', 'description', 'value',
        'stage', 'expected_close_date', 'actual_close_date', 'status', 'assigned_to',
    ];
    protected $casts = ['value' => 'decimal:3', 'expected_close_date' => 'date', 'actual_close_date' => 'date'];
    protected static function booted(): void { static::creating(fn(Opportunity $o) => $o->uuid ??= Str::uuid()); }

    public function customer() { return $this->belongsTo(Customer::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function scopeOpen($q) { return $q->where('status', 'open'); }
    public function scopeWon($q) { return $q->where('status', 'won'); }

    public function getStageLabelAttribute(): string {
        return match($this->stage) { 'qualification' => 'صلاحیت', 'proposal' => 'پیشنهاد', 'negotiation' => 'مذاکره', 'closed_won' => 'بسته شده (برد)', 'closed_lost' => 'بسته شده (باخت)', default => $this->stage };
    }
}
