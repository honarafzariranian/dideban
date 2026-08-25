<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToOrganization;

class Activity extends Model {
    use HasFactory, BelongsToOrganization;
    protected $fillable = [
        'organization_id', 'activityable_type', 'activityable_id', 'type',
        'subject', 'description', 'due_date', 'completed_at', 'assigned_to', 'created_by', 'status',
    ];
    protected $casts = ['due_date' => 'datetime', 'completed_at' => 'datetime'];

    public function activityable() { return $this->morphTo(); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function complete(): void {
        $this->update(['status' => 'completed', 'completed_at' => now()]);
    }

    public function getTypeLabelAttribute(): string {
        return match($this->type) { 'call' => 'تماس', 'meeting' => 'جلسه', 'task' => 'وظیفه', 'note' => 'یادداشت', 'email' => 'ایمیل', default => $this->type };
    }
}
