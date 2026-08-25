<?php

namespace App\Providers;

use App\Events\StockMovementCreated;
use App\Events\StockMovementApproved;
use App\Events\PurchaseOrderApproved;
use App\Listeners\SendStockMovementNotification;
use App\Listeners\LogStockMovement;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<class-string>>
     */
    protected $listen = [
        StockMovementCreated::class => [
            SendStockMovementNotification::class,
            LogStockMovement::class,
        ],
        StockMovementApproved::class => [
            // Add listeners here
        ],
        PurchaseOrderApproved::class => [
            // Add listeners here
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
