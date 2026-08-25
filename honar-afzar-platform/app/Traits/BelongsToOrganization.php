<?php

namespace App\Traits;

use App\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToOrganization
{
    /**
     * Boot the trait.
     */
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope());
    }

    /**
     * Get the organization ID from the current request.
     */
    public static function getCurrentOrganizationId(): ?int
    {
        if (request()->attributes->has('organization_id')) {
            return request()->attributes->get('organization_id');
        }

        if (auth()->check()) {
            return auth()->user()->organization_id;
        }

        return null;
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->withoutGlobalScope(OrganizationScope::class)
                     ->where('organization_id', $organizationId);
    }

    /**
     * Scope to filter by current organization.
     */
    public function scopeForCurrentOrganization(Builder $query): Builder
    {
        $organizationId = static::getCurrentOrganizationId();
        
        if ($organizationId) {
            return $query->where('organization_id', $organizationId);
        }
        
        return $query;
    }

    /**
     * Set the organization_id when creating a model.
     */
    public static function bootBelongsToOrganizationCreating(): void
    {
        static::creating(function (Model $model) {
            if (is_null($model->organization_id)) {
                $model->organization_id = static::getCurrentOrganizationId();
            }
        });
    }
}
