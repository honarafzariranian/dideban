<?php

namespace App\Providers;

use App\Models\Warehouse;
use App\Models\InventoryProduct;
use App\Models\StockMovement;
use App\Models\PurchaseOrder;
use App\Policies\WarehousePolicy;
use App\Policies\ProductPolicy;
use App\Policies\StockMovementPolicy;
use App\Policies\PurchaseOrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Warehouse::class => WarehousePolicy::class,
        InventoryProduct::class => ProductPolicy::class,
        StockMovement::class => StockMovementPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define admin gate
        Gate::define('admin', function ($user) {
            return $user->hasRole('super-admin') || $user->hasRole('org-admin');
        });

        // Define manager gate
        Gate::define('manager', function ($user) {
            return $user->hasRole('super-admin') || $user->hasRole('org-admin') || $user->hasRole('manager');
        });
    }
}
