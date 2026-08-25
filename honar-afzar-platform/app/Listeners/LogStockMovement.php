<?php

namespace App\Listeners;

use App\Events\StockMovementCreated;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogStockMovement implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected AuditService $auditService
    ) {}

    public function handle(StockMovementCreated $event): void
    {
        $this->auditService->log([
            'organization_id' => $event->organizationId,
            'user_id' => $event->movement->created_by,
            'auditable_type' => get_class($event->movement),
            'auditable_id' => $event->movement->id,
            'event' => 'created',
            'new_values' => $event->movement->toArray(),
            'tags' => ['stock_movement', 'inventory'],
        ]);
    }
}
