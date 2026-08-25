<?php

namespace App\Listeners;

use App\Events\StockMovementCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendStockMovementNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(StockMovementCreated $event): void
    {
        $movement = $event->movement;
        
        // Notify warehouse manager
        if ($movement->warehouse && $movement->warehouse->manager_id) {
            $this->notificationService->sendToUser(
                $movement->warehouse->manager,
                [
                    'type' => 'stock_movement',
                    'title' => 'حرکت انبار جدید',
                    'message' => "حرکت انبار {$movement->type_label} برای محصول {$movement->product->name} ثبت شد",
                    'data' => [
                        'movement_id' => $movement->id,
                        'warehouse_id' => $movement->warehouse_id,
                        'product_id' => $movement->product_id,
                        'type' => $movement->type,
                        'quantity' => $movement->quantity,
                    ],
                    'action_url' => "/inventory/stock-movements/{$movement->id}",
                    'action_text' => 'مشاهده',
                    'priority' => 'normal',
                    'group' => 'inventory',
                ]
            );
        }
    }
}
