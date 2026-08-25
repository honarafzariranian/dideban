<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\BelongsToOrganization;

class Correspondence extends Model {
    use HasFactory, SoftDeletes, BelongsToOrganization;
    protected $fillable = [
        'organization_id', 'uuid', 'type', 'reference_number', 'date', 'subject', 'body',
        'sender_name', 'recipient_name', 'from_department_id', 'to_department_id',
        'priority', 'status', 'deadline', 'created_by', 'approved_by', 'approved_at', 'metadata',
    ];
    protected $casts = ['date' => 'date', 'deadline' => 'date', 'approved_at' => 'datetime', 'metadata' => 'array'];
    protected static function booted(): void {
        static::creating(function (Correspondence $c) {
            $c->uuid ??= Str::uuid();
            $c->reference_number ??= static::generateNumber($c->organization_id, $c->type);
        });
    }

    public function fromDepartment() { return $this->belongsTo(Department::class, 'from_department_id'); }
    public function toDepartment() { return $this->belongsTo(Department::class, 'to_department_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function attachments() { return $this->belongsToMany(File::class, 'correspondence_attachments'); }

    public static function generateNumber(int $orgId, string $type): string {
        $prefix = match($type) { 'incoming' => 'IN', 'outgoing' => 'OUT', 'internal' => 'INT', default => 'COR' };
        $last = static::where('organization_id', $orgId)->where('type', $type)->latest('id')->first();
        $n = $last ? intval(substr($last->reference_number, 3)) + 1 : 1;
        return $prefix . '-' . str_pad($n, 6, '0', STR_PAD_LEFT);
    }

    public function approve(int $userId): void {
        if ($this->status !== 'pending') throw new \Exception('فقط نامه‌های در انتظار قابل تأیید هستند');
        $this->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);
    }

    public function getStatusLabelAttribute(): string {
        return match($this->status) { 'draft' => 'پیش‌نویس', 'pending' => 'در انتظار', 'approved' => 'تأیید شده', 'sent' => 'ارسال شده', 'received' => 'دریافت شده', 'archived' => 'بایگانی', 'rejected' => 'رد شده', default => $this->status };
    }
    public function getPriorityLabelAttribute(): string {
        return match($this->priority) { 'low' => 'پایین', 'normal' => 'عادی', 'high' => 'بالا', 'urgent' => 'فوری', default => $this->priority };
    }
}
