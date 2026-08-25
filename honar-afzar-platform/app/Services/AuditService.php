<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditService
{
    /**
     * Log an activity
     */
    public function log(array $data): ActivityLog
    {
        return DB::transaction(function () use ($data) {
            return ActivityLog::create([
                'uuid' => Str::uuid(),
                'organization_id' => $data['organization_id'],
                'user_id' => $data['user_id'] ?? null,
                'auditable_type' => $data['auditable_type'],
                'auditable_id' => $data['auditable_id'],
                'event' => $data['event'],
                'old_values' => $data['old_values'] ?? null,
                'new_values' => $data['new_values'] ?? null,
                'url' => $data['url'] ?? request()->url(),
                'ip_address' => $data['ip_address'] ?? request()->ip(),
                'user_agent' => $data['user_agent'] ?? request()->userAgent(),
                'tags' => $data['tags'] ?? null,
            ]);
        });
    }

    /**
     * Log model creation
     */
    public function logCreated(Model $model, ?User $user = null): ActivityLog
    {
        return $this->log([
            'organization_id' => $model->organization_id ?? $user?->organization_id,
            'user_id' => $user?->id ?? auth()->id(),
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'event' => 'created',
            'new_values' => $model->toArray(),
        ]);
    }

    /**
     * Log model update
     */
    public function logUpdated(Model $model, array $oldValues, ?User $user = null): ActivityLog
    {
        return $this->log([
            'organization_id' => $model->organization_id ?? $user?->organization_id,
            'user_id' => $user?->id ?? auth()->id(),
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'event' => 'updated',
            'old_values' => $oldValues,
            'new_values' => $model->toArray(),
        ]);
    }

    /**
     * Log model deletion
     */
    public function logDeleted(Model $model, ?User $user = null): ActivityLog
    {
        return $this->log([
            'organization_id' => $model->organization_id ?? $user?->organization_id,
            'user_id' => $user?->id ?? auth()->id(),
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'event' => 'deleted',
            'old_values' => $model->toArray(),
        ]);
    }

    /**
     * Get activity logs for a model
     */
    public function getModelLogs(string $type, int $id, int $limit = 50)
    {
        return ActivityLog::where('auditable_type', $type)
                         ->where('auditable_id', $id)
                         ->orderBy('created_at', 'desc')
                         ->limit($limit)
                         ->get();
    }

    /**
     * Get activity logs for an organization
     */
    public function getOrganizationLogs(int $organizationId, int $limit = 100)
    {
        return ActivityLog::where('organization_id', $organizationId)
                         ->orderBy('created_at', 'desc')
                         ->limit($limit)
                         ->get();
    }

    /**
     * Get activity logs for a user
     */
    public function getUserLogs(int $userId, int $limit = 100)
    {
        return ActivityLog::where('user_id', $userId)
                         ->orderBy('created_at', 'desc')
                         ->limit($limit)
                         ->get();
    }

    /**
     * Get activity logs by event type
     */
    public function getLogsByEvent(string $event, int $organizationId, int $limit = 100)
    {
        return ActivityLog::where('organization_id', $organizationId)
                         ->where('event', $event)
                         ->orderBy('created_at', 'desc')
                         ->limit($limit)
                         ->get();
    }

    /**
     * Clean old logs
     */
    public function cleanOldLogs(int $days = 365): int
    {
        return ActivityLog::where('created_at', '<', now()->subDays($days))
                         ->delete();
    }
}
