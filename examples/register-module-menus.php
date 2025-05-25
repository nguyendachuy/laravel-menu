<?php

/**
 * Example of how other modules can register their menu items to the menu builder
 */

// Trong file khởi động của module (ví dụ: ServiceProvider)
use NguyenHuy\Menu\MenuItemsRegistry;

class YourModuleServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        // Register menu items from your module
        $this->registerMenuItems();
    }
    
    protected function registerMenuItems()
    {
        // Example 1: Register a new section
        MenuItemsRegistry::registerSection('Your Module Pages', [
            [
                'url' => '/your-module/page1',
                'icon' => 'fa fa-file',
                'label' => 'Module Page 1',
            ],
            [
                'url' => '/your-module/page2',
                'icon' => 'fa fa-file',
                'label' => 'Module Page 2',
            ],
        ]);
        
        // Example 2: Add items to an existing section
        MenuItemsRegistry::addItemsToSection('Pages', [
            [
                'url' => '/your-module/custom-page',
                'icon' => 'fa fa-star',
                'label' => 'Custom Module Page',
            ],
        ]);
        
        // Example 3: Register dynamic menu items from database
        $this->registerDynamicMenuItems();
    }
    
    protected function registerDynamicMenuItems()
    {
        // Example of getting data from your model
        $pages = \YourModule\Models\Page::published()->get();
        
        $menuItems = [];
        foreach ($pages as $page) {
            $menuItems[] = [
                'url' => '/your-module/pages/' . $page->slug,
                'icon' => $page->icon ?? '',
                'label' => $page->title,
            ];
        }
        
        // Register dynamic menu items
        if (!empty($menuItems)) {
            MenuItemsRegistry::registerSection('Dynamic Pages', $menuItems);
        }
    }
}
