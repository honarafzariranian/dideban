<?php

namespace App\Policies;

use App\Models\User;
use App\Models\InventoryProduct;

class ProductPolicy
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
    public function view(User $user, InventoryProduct $product): bool
    {
        return $user->organization_id === $product->organization_id
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
    public function update(User $user, InventoryProduct $product): bool
    {
        return $user->organization_id === $product->organization_id
            && $user->can('andookhtiar.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InventoryProduct $product): bool
    {
        return $user->organization_id === $product->organization_id
            && $user->can('andookhtiar.delete');
    }
}
