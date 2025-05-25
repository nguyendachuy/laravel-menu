<?php

namespace NguyenHuy\Menu;

use NguyenHuy\Menu\Models\Menus;
use NguyenHuy\Menu\Models\MenuItems;
use NguyenHuy\Menu\MenuItemsRegistry;
use NguyenHuy\Menu\Repositories\MenuRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View as ViewInstance;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WMenu
{
    /**
     * Menu repository instance
     * 
     * @var MenuRepository
     */
    protected $menuRepository;

    /**
     * Constructor with optional dependency injection
     * 
     * @param MenuRepository|null $menuRepository
     */
    public function __construct(MenuRepository $menuRepository = null)
    {
        $this->menuRepository = $menuRepository ?? app(MenuRepository::class);
    }

    /**
     * Render the menu builder interface with isolation
     *
     * @return ViewInstance
     */
    public function render(): ViewInstance
    {
        // Get all menus for the dropdown
        $menulist = $this->getMenuListForDropdown();

        // Handle form submission or menu display
        if ((request()->has('action') && empty(request()->input('menu')))
            || request()->input('menu') == '0') {
            return view('nguyendachuy-menu::menu-html')->with(['menulist' => $menulist]);
        } else {
            return $this->renderMenuEditor($menulist);
        }
    }

    /**
     * Get menu list for dropdown
     * 
     * @return array
     */
    protected function getMenuListForDropdown(): array
    {
        $cacheKey = config('menu.cache_key_prefix') . ':menulist_dropdown';
        
        if (config('menu.cache_enabled')) {
            return Cache::remember($cacheKey, config('menu.cache_ttl'), function () {
                return $this->generateMenuListForDropdown();
            });
        }
        
        return $this->generateMenuListForDropdown();
    }

    /**
     * Generate menu list for dropdown
     * 
     * @return array
     */
    protected function generateMenuListForDropdown(): array
    {
        return Menus::select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->prepend(__('menu.select_menu'), 0)
            ->all();
    }

    /**
     * Render menu editor
     * 
     * @param array $menulist
     * @return ViewInstance
     */
    protected function renderMenuEditor(array $menulist): ViewInstance
    {
        $menuId = request()->input('menu');
        
        try {
            $menu = Menus::with('items')->findOrFail($menuId);
            $menus = $menu->getStructure();
            
            // Get all registered menu sections
            $menuSections = MenuItemsRegistry::getAllSections();
            
            $data = [
                'menus' => $menus, 
                'indmenu' => $menu, 
                'menulist' => $menulist,
                'menuSections' => $menuSections,
                'locale' => app()->getLocale(),
                'available_locales' => config('menu.localization.available_locales', [])
            ];
            
            // Add roles data if enabled
            if (config('menu.use_roles')) {
                $this->addRolesDataToViewData($data);
            }
            
            return view('nguyendachuy-menu::menu-html', $data);
        } catch (ModelNotFoundException $e) {
            // Handle case when menu is not found
            return view('nguyendachuy-menu::menu-html', [
                'menulist' => $menulist,
                'menuSections' => MenuItemsRegistry::getAllSections(),
                'error' => __('menu.menu_not_found'),
                'locale' => app()->getLocale(),
                'available_locales' => config('menu.localization.available_locales', [])
            ]);
        }
    }

    /**
     * Add roles data to view data
     * 
     * @param array &$data
     * @return void
     */
    protected function addRolesDataToViewData(array &$data): void
    {
        $cacheKey = config('menu.cache_key_prefix') . ':roles_data';
        
        if (config('menu.cache_enabled')) {
            $rolesData = Cache::remember($cacheKey, config('menu.cache_ttl'), function () {
                return $this->getRolesData();
            });
        } else {
            $rolesData = $this->getRolesData();
        }
        
        $data = array_merge($data, $rolesData);
    }

    /**
     * Get roles data
     * 
     * @return array
     */
    protected function getRolesData(): array
    {
        $roles = DB::table(config('menu.roles_table'))->select([
            config('menu.roles_pk'),
            config('menu.roles_title_field')
        ])->get();
        
        return [
            'roles' => $roles,
            'role_pk' => config('menu.roles_pk'),
            'role_title_field' => config('menu.roles_title_field')
        ];
    }

    /**
     * Include the menu scripts with isolation
     *
     * @return ViewInstance
     */
    public function scripts(): ViewInstance
    {
        // Ensure the correct locale is set
        app()->setLocale(config('menu.localization.enabled') ? config('app.locale') : config('menu.localization.default_locale'));
        
        return view('nguyendachuy-menu::scripts', [
            'locale' => app()->getLocale(),
            'translations' => __('menu')
        ]);
    }

    /**
     * Generate a select element for menus
     *
     * @param string $name
     * @param array $menulist
     * @param array $attributes
     * @return string
     */
    public function select(string $name = "menu", array $menulist = [], array $attributes = []): string
    {
        if (empty($menulist)) {
            $menulist = $this->getMenuListForDropdown();
        }
        
        $attribute_string = $this->buildHtmlAttributes($attributes);
        $html = "<select name=\"{$name}\" {$attribute_string}>";
        
        foreach ($menulist as $key => $val) {
            $active = request()->input('menu') == $key ? 'selected="selected"' : '';
            $html .= "<option {$active} value=\"{$key}\">{$val}</option>";
        }
        
        $html .= '</select>';
        return $html;
    }

    /**
     * Build HTML attributes string
     * 
     * @param array $attributes
     * @return string
     */
    protected function buildHtmlAttributes(array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }
        
        $attributePairs = [];
        foreach ($attributes as $key => $value) {
            $attributePairs[] = $key . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
        
        return implode(' ', $attributePairs);
    }

    /**
     * Get menu structure by menu name
     *
     * @param string $name
     * @param int|null $roleId
     * @return array
     */
    public static function getByName(string $name, ?int $roleId = null): array
    {
        try {
            $menu = Menus::findByName($name);
            
            if (!$menu) {
                return [];
            }
            
            if ($roleId !== null && config('menu.use_roles')) {
                $cacheKey = config('menu.cache_key_prefix') . ":menu:{$name}:role:{$roleId}";
                
                if (config('menu.cache_enabled')) {
                    return Cache::remember($cacheKey, config('menu.cache_ttl'), function () use ($menu, $roleId) {
                        return static::generateStructureByRole($menu, $roleId);
                    });
                }
                
                return static::generateStructureByRole($menu, $roleId);
            }
            
            return $menu->getStructure();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate menu structure filtered by role
     * 
     * @param Menus $menu
     * @param int $roleId
     * @return array
     */
    protected static function generateStructureByRole(Menus $menu, int $roleId): array
    {
        $items = $menu->getItemsByRole($roleId);
        
        return $items->map(function ($item) {
            return $item->toStructure();
        })->toArray();
    }

    /**
     * Get menu structure by menu ID
     *
     * @param int $menuId
     * @param int|null $roleId
     * @return array
     */
    public static function get(int $menuId, ?int $roleId = null): array
    {
        try {
            $menu = Menus::find($menuId);
            
            if (!$menu) {
                return [];
            }
            
            if ($roleId !== null && config('menu.use_roles')) {
                $cacheKey = config('menu.cache_key_prefix') . ":menu:{$menuId}:role:{$roleId}";
                
                if (config('menu.cache_enabled')) {
                    return Cache::remember($cacheKey, config('menu.cache_ttl'), function () use ($menu, $roleId) {
                        return static::generateStructureByRole($menu, $roleId);
                    });
                }
                
                return static::generateStructureByRole($menu, $roleId);
            }
            
            return $menu->getStructure();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Render frontend menu with isolated styles
     * 
     * @param string $menuName
     * @param string $view
     * @param array $data
     * @param int|null $roleId
     * @return ViewInstance|string
     */
    public static function renderMenu(string $menuName, string $view, array $data = [], ?int $roleId = null)
    {
        $menuData = static::getByName($menuName, $roleId);
        
        if (empty($menuData)) {
            return '';
        }
        
        // Add prefix to the view data
        $viewData = array_merge([
            'menu' => $menuData,
            'prefix' => config('menu.prefix', 'ndhuy-menu-'),
        ], $data);
        
        return view($view, $viewData);
    }

    /**
     * Clear all menu cache
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        if (config('menu.cache_enabled')) {
            Cache::forget(config('menu.cache_key_prefix') . ':menulist_dropdown');
            Cache::forget(config('menu.cache_key_prefix') . ':roles_data');
            
            // Clear individual menu caches
            $menus = Menus::all();
            foreach ($menus as $menu) {
                $menu->clearCache();
            }
        }
    }
}
