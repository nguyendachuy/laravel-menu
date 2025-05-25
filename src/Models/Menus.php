<?php

namespace NguyenHuy\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class Menus extends Model
{
    use Traits\QueryCacheTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'class'];

    /**
     * The cache key for menu
     * 
     * @var string
     */
    protected $cacheKey;

    /**
     * Constructor to set the table name from config.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('menu.table_prefix') . config('menu.table_name_menus');
        $this->cacheKey = config('menu.cache_key_prefix') . ':menus:';
    }

    /**
     * Scope to find menu by name.
     *
     * @param Builder $query
     * @param string $name
     * @return Builder
     */
    public function scopeByName(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }

    /**
     * Get all root-level menu items.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItems::class, 'menu')
            ->with('children')
            ->where('parent', 0)
            ->orderBy('sort', 'ASC');
    }

    /**
     * Get all menu items including children.
     *
     * @return HasMany
     */
    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItems::class, 'menu')
            ->orderBy('sort', 'ASC');
    }

    /**
     * Get menu structure as an array.
     *
     * @return array
     */
    public function getStructure(): array
    {
        if (config('menu.cache_enabled')) {
            return Cache::remember($this->cacheKey . $this->id, config('menu.cache_ttl'), function () {
                return $this->generateStructure();
            });
        }
        
        return $this->generateStructure();
    }

    /**
     * Generate menu structure from items
     * 
     * @return array
     */
    protected function generateStructure(): array
    {
        return $this->items->map(function ($item) {
            return $item->toStructure();
        })->toArray();
    }

    /**
     * Find a menu by name, with caching support
     *
     * @param string $name
     * @return self|null
     */
    public static function findByName(string $name): ?self
    {
        $cacheKey = config('menu.cache_key_prefix') . ':menu_by_name:' . $name;
        
        if (config('menu.cache_enabled')) {
            return Cache::remember($cacheKey, config('menu.cache_ttl'), function () use ($name) {
                return static::byName($name)->first();
            });
        }
        
        return static::byName($name)->first();
    }

    /**
     * Find menus by multiple names
     * 
     * @param array $names
     * @return Collection
     */
    public static function findByNames(array $names): Collection
    {
        return static::whereIn('name', $names)->get();
    }

    /**
     * Get items by role
     * 
     * @param int $roleId
     * @return Collection
     */
    public function getItemsByRole(int $roleId): Collection
    {
        return $this->hasMany(MenuItems::class, 'menu')
            ->byRole($roleId)
            ->with(['children' => function ($query) use ($roleId) {
                $query->byRole($roleId);
            }])
            ->where('parent', 0)
            ->orderBy('sort', 'ASC')
            ->get();
    }

    /**
     * Clear all cache related to this menu
     * 
     * @return void
     */
    public function clearCache(): void
    {
        if (config('menu.cache_enabled')) {
            Cache::forget($this->cacheKey . $this->id);
            Cache::forget(config('menu.cache_key_prefix') . ':menu_by_name:' . $this->name);
        }
    }
}
