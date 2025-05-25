# Laravel Drag and Drop Menu Builder
[![Latest Stable Version](https://poser.pugx.org/nguyendachuy/laravel-menu/v)](//packagist.org/packages/nguyendachuy/laravel-menu) [![Total Downloads](https://poser.pugx.org/nguyendachuy/laravel-menu/downloads)](//packagist.org/packages/nguyendachuy/laravel-menu) [![Latest Unstable Version](https://poser.pugx.org/nguyendachuy/laravel-menu/v/unstable)](//packagist.org/packages/nguyendachuy/laravel-menu) [![License](https://poser.pugx.org/nguyendachuy/laravel-menu/license)](//packagist.org/packages/nguyendachuy/laravel-menu)


![Laravel drag and drop menu](https://raw.githubusercontent.com/nguyendachuy/laravel-menu/master/screenshot.png)

A modern, responsive drag-and-drop menu builder for Laravel with role-based permissions, caching, and optimized JavaScript. The package features an intuitive drag-and-drop interface for creating and managing multi-level navigation menus.

## Features

- **Drag and Drop Menu Builder**: Intuitive interface with real-time visual feedback
- **Advanced Reordering System**: Seamlessly reorder menu items with automatic parent-child relationship updates
- **Multi-level Nested Menus**: Create unlimited depth navigation with proper hierarchy management
- **Role-based Permissions**: Restrict menu items to specific user roles
- **Modern JavaScript**: Built with jQuery and Nestable library for smooth interactions
- **Performance Optimized**: Database query optimizations and efficient data handling
- **Caching Support**: Optional caching system for improved performance
- **Responsive UI**: Clean, modern interface built with Tailwind CSS
- **Visual Feedback**: Enhanced visual cues during drag operations
- **Robust Error Handling**: Comprehensive validation and error recovery
- **Full Laravel Integration**: Seamless integration with Laravel's ecosystem

## Installation

### 1. Require the Package

```bash
composer require nguyendachuy/laravel-menu
```

### 2. Publish Assets and Configurations

```bash
php artisan vendor:publish --provider="NguyenHuy\Menu\Providers\MenuServiceProvider"
```

### 3. Configure the Package (optional)

Open `config/menu.php` to customize these settings:

- **CUSTOM MIDDLEWARE:** Add your own middleware for menu routes
- **TABLE PREFIX:** Customize database table prefix (default: admin_)
- **TABLE NAMES:** Change table names if needed
- **CUSTOM ROUTES:** Customize route paths
- **ROLE ACCESS:** Enable/disable role-based permissions on menu items
- **CACHE SETTINGS:**
  - `cache_enabled`: Enable/disable menu caching (default: false)
  - `cache_key_prefix`: Prefix for cache keys (default: 'menu')
  - `cache_ttl`: Time-to-live for cached items in minutes (default: 60)

### 4. Run Database Migrations

```bash
php artisan migrate
```

## Usage

### Basic Menu Builder Interface

Add to your blade template:

```php
@extends('app')

@section('contents')
    {!! Menu::render() !!}
@endsection

{{-- Add scripts at the end of your body --}}
@push('scripts')
    {!! Menu::scripts() !!}
@endpush
```

### Using the Models

```php
use NguyenHuy\Menu\Models\Menus;
use NguyenHuy\Menu\Models\MenuItems;
```

### Retrieving Menus

#### Using Model Classes

```php
// Get menu by ID
$menu = Menus::find(1);

// Or by name
$menu = Menus::where('name', 'Main Navigation')->first();

// With eager loading (recommended for better performance)
$menu = Menus::where('name', 'Main Navigation')->with('items')->first();

// Access menu items
$menuItems = $menu->items;

// Or convert to array
$menuItemsArray = $menu->items->toArray();
```

#### Using Helper Functions

```php
// Get menu structure by name
$menuItems = Menu::getByName('Main Navigation');

// Get menu structure by ID
$menuItems = Menu::get(1);

// Get menu with role filtering
$menuItems = Menu::getByName('Main Navigation', $roleId);

// Render menu with a custom view
echo Menu::renderMenu('Main Navigation', 'partials.menu', ['extraData' => $data]);
```

### Cache Management

```php
// Clear all menu cache
Menu::clearCache();

// Clear specific menu cache
$menu = Menus::find(1);
$menu->clearCache();
```

### Display Menu in Blade Templates

```php
<nav class="main-navigation">
    <ul class="menu">
        @if(isset($menuItems) && count($menuItems) > 0)
            @foreach($menuItems as $item)
                <li class="{{ $item['class'] }}">
                    <a href="{{ $item['link'] }}" target="{{ $item['target'] }}" 
                       @if($item['icon']) class="has-icon" @endif>
                        @if($item['icon'])
                            <i class="{{ $item['icon'] }}"></i>
                        @endif
                        {{ $item['label'] }}
                    </a>
                    
                    @if(isset($item['child']) && count($item['child']) > 0)
                        <ul class="sub-menu">
                            @foreach($item['child'] as $child)
                                <li class="{{ $child['class'] }}">
                                    <a href="{{ $child['link'] }}" target="{{ $child['target'] }}">
                                        @if($child['icon'])
                                            <i class="{{ $child['icon'] }}"></i>
                                        @endif
                                        {{ $child['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        @endif
    </ul>
</nav>
```

### Advanced Menu Methods

Get menu items filtered by role:

```php
$menu = Menus::findByName('Admin Panel');
$menuItems = $menu->getItemsByRole($roleId);
```

Get the full path of a menu item:

```php
$menuItem = MenuItems::find(5);
echo $menuItem->getFullPath(); // Parent > Child > Current Item
```

Check if a menu item has children:

```php
if ($menuItem->hasChildren()) {
    // Do something with parent items
}
```

Reorder menu items:

```php
$menuItem->moveUp(); // Move item up in sort order
$menuItem->moveDown(); // Move item down in sort order
```

### Parent-Child Relationships

The package correctly tracks and maintains parent-child relationships:

```php
// Get a menu item's direct parent
$parent = $menuItem->parentItem;

// Get all siblings of an item
$siblings = $menuItem->getSiblings();

// Get a menu item's depth level
$depth = $menuItem->depth;

// Get children of a menu item
$children = $menuItem->children;
```

## Registering Menu Items from Other Modules

One of the most powerful features of this package is the ability for other modules to register their own menu items. This allows you to create a centralized menu management system where each module can contribute its own menu items.

### Using the MenuItemsRegistry

The package provides a `MenuItemsRegistry` class that allows other modules to register their menu items:

```php
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
        // Register a new section
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
        
        // Add items to an existing section
        MenuItemsRegistry::addItemsToSection('Pages', [
            [
                'url' => '/your-module/custom-page',
                'icon' => 'fa fa-star',
                'label' => 'Custom Module Page',
            ],
        ]);
    }
}
```

### Registering Dynamic Menu Items

You can also register menu items dynamically from your database:

```php
// Get data from your model
$pages = YourModule\Models\Page::published()->get();

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
```

### Available Registration Methods

- `registerSection(string $name, array $items, bool $show = false)`: Register a new menu section
- `addItemsToSection(string $name, array $items)`: Add items to an existing section
- `getSection(string $name)`: Get a specific section by name
- `getAllSections()`: Get all registered sections
- `hasSection(string $name)`: Check if a section exists

### Customization

You can customize the menu interface by editing these views:

- `resources/views/vendor/nguyendachuy-menu/menu-html.blade.php`
- `resources/views/vendor/nguyendachuy-menu/partials/left.blade.php`
- `resources/views/vendor/nguyendachuy-menu/partials/right.blade.php`
- `resources/views/vendor/nguyendachuy-menu/partials/loop-item.blade.php`

You can also customize the CSS styles in:
- `public/vendor/nguyendachuy-menu/style.css`

## Frontend Customization

The package includes modern styles with CSS variables that can be overridden in your own CSS:

```css
:root {
  --menu-primary: #3b82f6;
  --menu-primary-hover: #2563eb;
  --menu-danger: #ef4444;
  --menu-success: #10b981;
  --menu-warning: #f59e0b;
  /* ...and more variables */
}
```

## Loading Indicator

The package features a modern top-of-page loading indicator that provides visual feedback during AJAX operations:

```css
#ajax_loader {
  box-shadow: 0 0 10px rgba(59, 130, 246, 0.7);
}
```

You can customize its appearance by overriding these CSS classes.

## Security and Error Handling

This package includes:

- CSRF protection for all AJAX requests
- Form input validation
- Error handling for database operations
- Informative notifications for success/error states
- Robust fallback mechanisms for asset loading

## Database Optimizations

The package includes database optimizations:

- Indexes on frequently queried columns
- Composite indexes for common query patterns
- Efficient depth and parent-child tracking
- Query caching options

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the MIT license.
