<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PurchaseOrder;

class PurchaseOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('andookhtiar.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseOrder $order): bool
    {
        return $user->organization_id === $order->organization_id
            && $user->can('andookhtiar.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('andookhtiar.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseOrder $order): bool
    {
        return $user->organization_id === $order->organization_id
            && $user->can('andookhtiar.update')
            && in_array($order->status, ['draft', 'pending']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseOrder $order): bool
    {
        return $user->organization_id === $order->organization_id
            && $user->can('andookhtiar.delete')
            && $order->status === 'draft';
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, PurchaseOrder $order): bool
    {
        return $user->organization_id === $order->organization_id
            && $user->can('andookhtiar.update')
            && $order->status === 'pending';
    }

    /**
     * Determine whether the user can receive the model.
     */
    public function receive(User $user, PurchaseOrder $order): bool
    {
        return $user->organization_id === $order->organization_id
            && $user->can('andookhtiar.update')
            && in_array($order->status, ['approved', 'partial']);
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, PurchaseOrder $order): bool
    {
        return $user->organization_id === $order->organization_id
            && $user->can('andookhtiar.update')
            && !in_array($order->status, ['received', 'cancelled']);
    }
}
