<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StockMovement;

class StockMovementPolicy
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
    public function view(User $user, StockMovement $movement): bool
    {
        return $user->organization_id === $movement->organization_id
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
    public function update(User $user, StockMovement $movement): bool
    {
        return $user->organization_id === $movement->organization_id
            && $user->can('andookhtiar.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StockMovement $movement): bool
    {
        return false; // Stock movements should never be deleted
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, StockMovement $movement): bool
    {
        return $user->organization_id === $movement->organization_id
            && $user->can('andookhtiar.update')
            && $movement->status === 'pending';
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, StockMovement $movement): bool
    {
        return $user->organization_id === $movement->organization_id
            && $user->can('andookhtiar.update')
            && $movement->status === 'pending';
    }
}
