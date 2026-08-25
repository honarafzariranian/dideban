<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Relations\Relation;

class OrganizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $organizationId = $this->getOrganizationId();

        if ($organizationId) {
            $builder->where($model->getTable() . '.organization_id', $organizationId);
        }
    }

    /**
     * Get the organization ID from the current request or session.
     */
    protected function getOrganizationId(): ?int
    {
        // Get from request attributes (set by middleware)
        if (request()->attributes->has('organization_id')) {
            return request()->attributes->get('organization_id');
        }

        // Get from authenticated user
        if (auth()->check()) {
            return auth()->user()->organization_id;
        }

        return null;
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     */
    public function remove(Builder $builder, Model $model): void
    {
        $query = $builder->getQuery();

        foreach ($query->wheres as $key => $where) {
            if ($where['column'] === $model->getTable() . '.organization_id') {
                unset($query->wheres[$key]);
                $query->wheres = array_values($query->wheres);
            }
        }
    }

    /**
     * Check if the scope has been applied to the given query builder.
     */
    public function isApplied(Builder $builder): bool
    {
        foreach ($builder->getQuery()->wheres as $where) {
            if (isset($where['column']) && $where['column'] === $builder->getModel()->getTable() . '.organization_id') {
                return true;
            }
        }

        return false;
    }
}
