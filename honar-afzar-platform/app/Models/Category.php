<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToOrganization;

class Category extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'parent_id',
        'name',
        'code',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(InventoryProduct::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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

    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }
}
