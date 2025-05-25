<?php

namespace NguyenHuy\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MenuItems extends Model
{
    use Traits\QueryCacheTrait;
    // Removed HasFactory trait that was causing the error
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label', 'link', 'parent', 'sort', 'class', 
        'menu', 'depth', 'role_id', 'icon', 'target',
        'is_mega_menu', 'mega_menu_content'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'parent' => 'integer',
        'sort' => 'integer',
        'depth' => 'integer',
        'menu' => 'integer',
        'role_id' => 'integer',
    ];

    /**
     * Constructor to set table name from config.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('menu.table_prefix') . config('menu.table_name_items');
    }

    /**
     * Get children menu items.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent')
            ->with('children')
            ->orderBy('sort', 'ASC');
    }

    /**
     * Get parent menu
     *
     * @return BelongsTo
     */
    public function parentMenu(): BelongsTo
    {
        return $this->belongsTo(Menus::class, 'menu');
    }

    /**
     * Get parent menu item
     *
     * @return BelongsTo
     */
    public function parentItem(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent');
    }

    /**
     * Get all items for a specific menu.
     *
     * @param int $menuId
     * @return Collection
     */
    public static function getAllItems(int $menuId): Collection
    {
        return static::where('menu', $menuId)
            ->orderBy('sort', 'asc')
            ->get();
    }

    /**
     * Get direct children of an item.
     *
     * @param int $parentId
     * @return Collection
     */
    public static function getChildren(int $parentId): Collection
    {
        return static::where('parent', $parentId)
            ->orderBy('sort', 'asc')
            ->get();
    }

    /**
     * Get the next available sort value for root items.
     *
     * @param int $menuId
     * @return int
     */
    public static function getNextSortRoot(int $menuId): int
    {
        return (int) static::where('menu', $menuId)->max('sort') + 1;
    }

    /**
     * Convert item to structure array with children.
     *
     * @return array
     */
    public function toStructure(): array
    {
        $structure = $this->toArray();
        
        if ($this->children->count() > 0) {
            $structure['child'] = $this->children->map(function ($child) {
                return $child->toStructure();
            })->toArray();
        } else {
            $structure['child'] = [];
        }
        
        return $structure;
    }

    /**
     * Scope query to filter by role.
     *
     * @param Builder $query
     * @param int $roleId
     * @return Builder
     */
    public function scopeByRole(Builder $query, int $roleId): Builder
    {
        if ($roleId === 0) {
            return $query->where(function (Builder $q) {
                $q->whereNull('role_id')
                  ->orWhere('role_id', 0);
            });
        }
        
        return $query->where(function (Builder $q) use ($roleId) {
            $q->where('role_id', $roleId)
              ->orWhere('role_id', 0)
              ->orWhereNull('role_id');
        });
    }

    /**
     * Get siblings of current item
     * 
     * @return Collection
     */
    public function getSiblings(): Collection
    {
        return static::where('menu', $this->menu)
            ->where('parent', $this->parent)
            ->where('id', '!=', $this->id)
            ->orderBy('sort', 'asc')
            ->get();
    }

    /**
     * Move item up in sort order
     * 
     * @return bool
     */
    public function moveUp(): bool
    {
        $prevItem = static::where('menu', $this->menu)
            ->where('parent', $this->parent)
            ->where('sort', '<', $this->sort)
            ->orderBy('sort', 'desc')
            ->first();

        if ($prevItem) {
            $tempSort = $prevItem->sort;
            $prevItem->sort = $this->sort;
            $this->sort = $tempSort;

            $prevItem->save();
            $this->save();
            
            return true;
        }
        
        return false;
    }

    /**
     * Move item down in sort order
     * 
     * @return bool
     */
    public function moveDown(): bool
    {
        $nextItem = static::where('menu', $this->menu)
            ->where('parent', $this->parent)
            ->where('sort', '>', $this->sort)
            ->orderBy('sort', 'asc')
            ->first();

        if ($nextItem) {
            $tempSort = $nextItem->sort;
            $nextItem->sort = $this->sort;
            $this->sort = $tempSort;

            $nextItem->save();
            $this->save();
            
            return true;
        }
        
        return false;
    }

    /**
     * Check if this item has children
     * 
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Get the full path for the menu item
     * 
     * @return string
     */
    public function getFullPath(): string
    {
        $path = $this->label;
        $parent = $this->parentItem;
        
        while ($parent) {
            $path = $parent->label . ' > ' . $path;
            $parent = $parent->parentItem;
        }
        
        return $path;
    }
}
