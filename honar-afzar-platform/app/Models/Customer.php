<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\BelongsToOrganization;

class Customer extends Model {
    use HasFactory, SoftDeletes, BelongsToOrganization;
    protected $fillable = [
        'organization_id', 'uuid', 'name', 'code', 'company_name', 'contact_person',
        'email', 'phone', 'mobile', 'address', 'city', 'national_id', 'tax_number',
        'credit_limit', 'current_balance', 'status', 'score', 'metadata',
    ];
    protected $casts = [
        'credit_limit' => 'decimal:3', 'current_balance' => 'decimal:3', 'metadata' => 'array',
    ];
    protected static function booted(): void { static::creating(fn(Customer $c) => $c->uuid ??= Str::uuid()); }

    public function opportunities() { return $this->hasMany(Opportunity::class); }
    public function activities() { return $this->morphMany(Activity::class, 'activityable'); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeLeads($q) { return $q->where('status', 'lead'); }

    public function getStatusLabelAttribute(): string {
        return match($this->status) { 'lead' => 'سرنخ', 'prospect' => '潜在', 'active' => 'فعال', 'inactive' => 'غیرفعال', default => $this->status };
    }
}
