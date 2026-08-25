<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'parent_id',
        'name',
        'code',
        'description',
        'manager_id',
        'sort_order',
        'status',
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // Helper Methods
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function getChildrenRecursive()
    {
        return $this->children()->with('getChildrenRecursive')->get();
    }

    public function getFullHierarchyAttribute(): string
    {
        $parts = [];
        $current = $this;
        
        while ($current) {
            $parts[] = $current->name;
            $current = $current->parent;
        }
        
        return implode(' > ', array_reverse($parts));
    }
}
