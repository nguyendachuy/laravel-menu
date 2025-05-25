<?php

namespace NguyenHuy\Menu\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use NguyenHuy\Menu\Models\Menus;
use NguyenHuy\Menu\Models\MenuItems;

class MenuRepository
{
    /**
     * Create a new menu
     * 
     * @param array $data
     * @return Menus
     */
    public function createMenu(array $data): Menus
    {
        $menu = Menus::create([
            'name' => $data['name'],
            'class' => $data['class'] ?? null
        ]);
        
        $this->clearMenuCache();
        
        return $menu;
    }
    
    /**
     * Delete a menu
     * 
     * @param Menus $menu
     * @return bool
     */
    public function deleteMenu(Menus $menu): bool
    {
        $this->clearMenuCache();
        return $menu->delete();
    }
    
    /**
     * Update menu properties
     * 
     * @param int $menuId
     * @param array $data
     * @return Menus
     */
    public function updateMenu(int $menuId, array $data): Menus
    {
        $menu = Menus::findOrFail($menuId);
        $menu->update([
            'name' => $data['name'],
            'class' => $data['class'] ?? null
        ]);
        
        $this->clearMenuCache();
        
        return $menu;
    }
    
    /**
     * Create multiple menu items
     * 
     * @param array $items
     * @return void
     */
    public function createMenuItems(array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                MenuItems::create([
                    'label' => $item['label'],
                    'link' => $item['url'],
                    'icon' => $item['icon'] ?? null,
                    'role_id' => config('menu.use_roles') ? ($item['role'] ?? 0) : 0,
                    'menu' => $item['id'],
                    'sort' => MenuItems::getNextSortRoot($item['id'])
                ]);
            }
        });
        
        $this->clearMenuCache();
    }
    
    /**
     * Update menu structure and order
     * 
     * @param array $items
     * @return void
     */
    public function updateMenuItems(array $items): void
    {
        DB::transaction(function () use ($items) {
            $this->processMenuItems($items);
        });
        
        $this->clearMenuCache();
    }
    
    /**
     * Process menu items recursively
     * 
     * @param array $items
     * @param int $parentId
     * @param int $order
     * @return void
     */
    protected function processMenuItems(array $items, int $parentId = 0, int $order = 0): void
    {
        foreach ($items as $key => $item) {
            // Ensure we have the required data
            if (!isset($item['id'])) {
                continue;
            }
            
            // Update the current item
            MenuItems::where('id', $item['id'])->update([
                'parent' => $parentId,
                'sort' => $order + $key,
                'depth' => $item['depth'] ?? ($parentId > 0 ? 2 : 1) // Use provided depth or calculate based on parent
            ]);
            
            // Process child items if they exist
            if (isset($item['children']) && is_array($item['children']) && !empty($item['children'])) {
                $this->processMenuItems($item['children'], $item['id'], 0);
            }
        }
    }
    
    /**
     * Update a single menu item
     * 
     * @param array $data
     * @return MenuItems
     */
    public function updateMenuItem(array $data): MenuItems
    {
        $menuItem = MenuItems::findOrFail($data['id']);
        
        // Check if this is a reordering operation (only id, sort, parent, depth provided)
        $isReordering = isset($data['sort']) && !isset($data['label']) && !isset($data['url']);
        
        if ($isReordering) {
            // For reordering, only update position-related fields
            $updateData = [];
            
            // Add sort information if provided
            if (isset($data['sort'])) {
                $updateData['sort'] = $data['sort'];
            }
            
            // Add parent information if provided
            if (isset($data['parent'])) {
                $updateData['parent'] = $data['parent'];
            }
            
            // Add depth information if provided
            if (isset($data['depth'])) {
                $updateData['depth'] = $data['depth'];
            }
        } else {
            // For regular updates, include all fields
            $updateData = [
                'label' => $data['label'],
                'link' => $data['url'],
                'class' => $data['clases'] ?? null,
                'icon' => $data['icon'] ?? null,
                'target' => $data['target']
            ];
            
            // Add depth information if provided
            if (isset($data['depth'])) {
                $updateData['depth'] = $data['depth'];
            }
            
            // Add parent information if provided
            if (isset($data['parent'])) {
                $updateData['parent'] = $data['parent'];
            }
            
            // Add sort information if provided
            if (isset($data['sort'])) {
                $updateData['sort'] = $data['sort'];
            }
            
            if (config('menu.use_roles')) {
                $updateData['role_id'] = isset($data['role_id']) ? $data['role_id'] : 0;
            }
        }
        
        $menuItem->update($updateData);
        
        $this->clearMenuCache();
        
        return $menuItem;
    }
    
    /**
     * Bulk update menu items
     * 
     * @param array $items
     * @return void
     */
    public function bulkUpdateMenuItems(array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $updateData = [
                    'label' => $item['label'],
                    'link' => $item['link'],
                    'class' => $item['class'] ?? null,
                    'icon' => $item['icon'] ?? null,
                    'target' => $item['target']
                ];
                
                // Add depth information if provided
                if (isset($item['depth'])) {
                    $updateData['depth'] = $item['depth'];
                }
                
                // Add parent information if provided
                if (isset($item['parent'])) {
                    $updateData['parent'] = $item['parent'];
                }
                
                if (config('menu.use_roles')) {
                    $updateData['role_id'] = $item['role_id'] ? $item['role_id'] : 0;
                }
                
                MenuItems::where('id', $item['id'])->update($updateData);
            }
        });
        
        $this->clearMenuCache();
    }
    
    /**
     * Delete a menu item
     * 
     * @param int $id
     * @return bool
     */
    public function deleteMenuItem(int $id): bool
    {
        $success = DB::transaction(function () use ($id) {
            // Delete child items first
            MenuItems::where('parent', $id)->delete();
            
            // Then delete the parent item
            return MenuItems::where('id', $id)->delete();
        });
        
        $this->clearMenuCache();
        
        return $success;
    }
    
    /**
     * Clear menu cache
     * 
     * @return void
     */
    protected function clearMenuCache(): void
    {
        if (config('menu.cache_enabled')) {
            Cache::forget(config('menu.cache_key_prefix'));
        }
    }
    
    /**
     * Reorder menu items based on new positions
     * 
     * @param array $items Array of items with their new positions
     * @return void
     */
    public function reorderMenuItems(array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $index => $itemId) {
                MenuItems::where('id', $itemId)->update([
                    'sort' => $index
                ]);
            }
        });
        
        $this->clearMenuCache();
    }
}
