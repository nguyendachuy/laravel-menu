# Laravel Drag and Drop Menu Builder
[![Latest Stable Version](https://poser.pugx.org/nguyendachuy/laravel-menu/v)](//packagist.org/packages/nguyendachuy/laravel-menu) [![Total Downloads](https://poser.pugx.org/nguyendachuy/laravel-menu/downloads)](//packagist.org/packages/nguyendachuy/laravel-menu) [![Latest Unstable Version](https://poser.pugx.org/nguyendachuy/laravel-menu/v/unstable)](//packagist.org/packages/nguyendachuy/laravel-menu) [![License](https://poser.pugx.org/nguyendachuy/laravel-menu/license)](//packagist.org/packages/nguyendachuy/laravel-menu)


![Laravel drag and drop menu](https://raw.githubusercontent.com/nguyendachuy/laravel-menu/upgrade-version/screenshot.png)

A modern, responsive drag-and-drop menu builder for Laravel with role-based permissions, caching, and optimized JavaScript. The package features an intuitive drag-and-drop interface for creating and managing multi-level navigation menus.

## Features

- **Drag and Drop Menu Builder**: Intuitive interface with real-time visual feedback
- **Advanced Reordering System**: Seamlessly reorder menu items with automatic parent-child relationship updates
- **Multi-level Nested Menus**: Create unlimited depth navigation with proper hierarchy management
- **Mega Menu Support**: Create rich mega menus with custom content and layouts
- **Role-based Permissions**: Restrict menu items to specific user roles
- **Multi-language Support**: Interface available in multiple languages (English, Vietnamese)
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
- **MENU TABLE PREFIX:** Change the database table prefix (default: 'admin_')
- **MENU CACHE ENABLED:** Enable/disable menu caching (default: true)
- **MENU CACHE TTL:** Set cache time-to-live in minutes (default: 1440)
- **ROLES ENABLED:** Enable/disable role-based menu permissions (default: false)
- **LOCALIZATION ENABLED:** Enable/disable multi-language support (default: true)

### 4. Run Migrations

```bash
php artisan migrate
```

## Basic Usage

### 1. Access the Menu Manager

Visit `/admin/menus` in your browser to access the menu manager interface.

### 2. Create a Menu

1. Enter a name for your menu in the "Create New Menu" section
2. Click "Create Menu"

### 3. Add Menu Items

1. Use the left panel to add new menu items
2. Fill in the label and URL
3. Click "Add to Menu"

### 4. Organize Menu Items

1. Drag and drop items to reorder them
2. Drag items slightly to the right to create child items
3. Click "Update Menu" to save changes

### 5. Display the Menu in Your Views

```php
@if(Menu::exists('main-menu'))
    {!! Menu::render('main-menu') !!}
@endif
```

## Advanced Usage

### Customizing Menu Output

You can customize the menu output by passing a custom view name:

```php
{!! Menu::render('main-menu', 'custom-menu-template') !!}
```

### Adding Custom Attributes

```php
{!! Menu::render('main-menu', null, ['class' => 'custom-menu', 'id' => 'main-navigation']) !!}
```

### Using Multi-language Support

The package supports multiple languages for the admin interface. By default, English and Vietnamese are included.

1. **Enable Localization**

   Make sure localization is enabled in your `config/menu.php` file:

   ```php
   'localization' => [
       'enabled' => true,
       'default_locale' => 'en',
       'available_locales' => [
           'en' => 'English',
           'vi' => 'Tiếng Việt',
       ],
   ],
   ```

2. **Add Your Own Languages**

   You can add your own language files by publishing the translation files:

   ```bash
   php artisan vendor:publish --tag=laravel-menu-translations
   ```

   Then create new language files in the `resources/lang/vendor/menu` directory or modify the existing ones in `resources/lang/en/menu.php` and `resources/lang/vi/menu.php`.

3. **Switch Languages**

   You can switch languages using Laravel's built-in localization features:

   ```php
   // In a controller or middleware
   App::setLocale('vi'); // Switch to Vietnamese
   ```

4. **Structure of Language Files**

   The language files contain translations for all user interface elements in the menu manager. Here's an example structure:

   ```php
   return [
       // Menu operations
       'select_menu' => 'Select Menu',
       'create_new_menu' => 'Create New Menu',
       'delete_menu' => 'Delete Menu',
       'update_menu' => 'Update Menu',
       
       // Form labels
       'label' => 'Label',
       'url' => 'URL',
       'icon' => 'Icon',
       'role' => 'Role',
       
       // Notifications
       'menu_created' => 'Menu created successfully',
       'menu_updated' => 'Menu updated successfully',
       'menu_deleted' => 'Menu deleted successfully',
       
       // JavaScript translations
       'confirm_delete' => 'Are you sure you want to delete this item?',
       'yes' => 'Yes',
       'no' => 'No',
   ];
   ```

5. **JavaScript Integration**

   The package automatically passes translations to JavaScript, making it possible to use translated strings in client-side code. This is handled through a data attribute that contains all translations:

   ```html
   <script id="menu-translations" type="application/json" data-translations='@json(__('menu'))'></script>
   ```

   In your JavaScript, you can access these translations using the global `__()` function:

   ```javascript
   // Get a translated string
   const message = __('menu.confirm_delete');
   
   // With replacements
   const welcomeMessage = __('menu.welcome', {name: 'John'});
   ```

### Using Role-based Permissions

Enable roles in `config/menu.php`:

```php
'use_roles' => true,
```

Then configure the role model and fields:

```php
'role_model' => 'App\Models\Role',
'role_pk' => 'id', // Primary key of the role model
'role_title_field' => 'name', // Display name field of the role model
```

## Mega Menu Support

This package includes support for creating rich mega menus with custom content. Mega menus are useful for displaying complex navigation structures with multiple columns, images, and other rich content.

### Using Mega Menus

1. Edit any menu item
2. Check the "Enable Mega Menu" option
3. Add your custom content in the mega menu content area
4. Save changes

### Integrating WYSIWYG Editors

You can easily integrate WYSIWYG editors like CKEditor, TinyMCE, or Summernote with the mega menu content area. Here's how:

#### 1. Publish the Views

First, publish the package views:

```bash
php artisan vendor:publish --provider="NguyenHuy\Menu\Providers\MenuServiceProvider" --tag="views"
```

#### 2. Integrate CKEditor (Example)

Edit the `resources/views/vendor/nguyendachuy-menu/partials/loop-item.blade.php` file:

```html
<!-- Find the mega menu content textarea -->
<div class="mb-3 mega-menu-content-container {{($item['is_mega_menu'] ?? false) ? '' : 'hidden'}}" id="mega-menu-content-{{$item['id']}}">
    <label class="block mb-1 text-sm font-medium text-gray-700" for="mega-menu-content-{{$item['id']}}">Mega Menu Content</label>
    <textarea id="mega-menu-content-{{$item['id']}}" rows="5" class="border border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 edit-menu-item-mega-content ckeditor-mega-menu">{{$item['mega_menu_content'] ?? ''}}</textarea>
    <p class="text-xs text-gray-500 mt-1">You can use HTML to create columns, lists, and other content for your mega menu.</p>
</div>
```

#### 3. Add JavaScript to Initialize the Editor

Add this to your custom JavaScript file or create a new one:

```javascript
// Example for CKEditor
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CKEditor on all mega menu content areas
    const megaMenuEditors = document.querySelectorAll('.ckeditor-mega-menu');
    if (megaMenuEditors.length > 0) {
        megaMenuEditors.forEach(function(editor) {
            CKEDITOR.replace(editor.id, {
                height: 200,
                toolbar: [
                    { name: 'document', items: ['Source'] },
                    { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                    { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll'] },
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
                    { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
                    { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                    { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
                    { name: 'colors', items: ['TextColor', 'BGColor'] },
                    { name: 'tools', items: ['Maximize', 'ShowBlocks'] }
                ]
            });
        });
    }
});
```

#### 4. Include the Required Scripts

Add the necessary scripts to your layout or view:

```html
<!-- For CKEditor -->
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script src="path/to/your/custom-mega-menu.js"></script>
```

#### 5. Alternative Editors

You can use any WYSIWYG editor of your choice. Here are examples for other popular editors:

##### TinyMCE

```javascript
// Initialize TinyMCE
tinymce.init({
    selector: '.edit-menu-item-mega-content',
    height: 200,
    plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
    ],
    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
});
```

##### Summernote

```javascript
// Initialize Summernote
$(document).ready(function() {
    $('.edit-menu-item-mega-content').summernote({
        height: 200,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
});
```

### Rendering Mega Menus

When rendering your menu, the mega menu content will be available in the `mega_menu_content` field of each menu item. You can use this to create custom mega menu layouts in your views.

Example custom view for rendering a menu with mega menu support:

```php
@foreach($menuItems as $item)
    @if($item['is_mega_menu'])
        <li class="mega-menu-container {{ $item['class'] }}">
            <a href="{{ $item['link'] }}" target="{{ $item['target'] }}">
                @if($item['icon'])
                    <i class="{{ $item['icon'] }}"></i>
                @endif
                {{ $item['label'] }}
            </a>
            <div class="mega-menu-content">
                {!! $item['mega_menu_content'] !!}
            </div>
        </li>
    @else
        <li class="{{ $item['class'] }}">
            <a href="{{ $item['link'] }}" target="{{ $item['target'] }}">
                @if($item['icon'])
                    <i class="{{ $item['icon'] }}"></i>
                @endif
                {{ $item['label'] }}
            </a>
            @if(isset($item['children']) && count($item['children']) > 0)
                <ul>
                    @include('nguyendachuy-menu::partials.menu-items', ['menuItems' => $item['children']])
                </ul>
            @endif
        </li>
    @endif
@endforeach
```

## Customization

You can customize the views by publishing them and modifying the published files:

```bash
php artisan vendor:publish --provider="NguyenHuy\Menu\Providers\MenuServiceProvider" --tag="views"
```

This will publish the views to `resources/views/vendor/nguyendachuy-menu/` where you can edit them.

The main views you might want to customize are:

- `resources/views/vendor/nguyendachuy-menu/menu-html.blade.php`
- `resources/views/vendor/nguyendachuy-menu/partials/left.blade.php`
- `resources/views/vendor/nguyendachuy-menu/partials/right.blade.php`
- `resources/views/vendor/nguyendachuy-menu/partials/loop-item.blade.php`

You can also customize the CSS styles in:
- `public/vendor/nguyendachuy-menu/style.css`

## Frontend Customization

The package includes modern styles built with Tailwind CSS. You can customize the appearance by overriding the CSS classes in your own stylesheet:

```css
/* Example of customizing menu styles */
.dd-item .dd-handle {
  background-color: #3b82f6; /* Change menu item background */
}

.dd-item .dd-handle:hover {
  background-color: #2563eb; /* Change menu item hover state */
}

.menu-item-settings {
  background-color: #f9fafb; /* Change settings panel background */
  border-color: #e5e7eb; /* Change border color */
}
```

## Loading Indicator

The package features a modern top-of-page loading indicator that provides visual feedback during AJAX operations. You can customize its appearance by overriding these CSS classes in your stylesheet:

```css
#ajax_loader {
  background-color: #3b82f6; /* Change the color of the loading bar */
  box-shadow: 0 0 10px rgba(59, 130, 246, 0.7); /* Change the glow effect */
  height: 3px; /* Change the height of the loading bar */
}

#ajax_loader.loading {
  animation: loadingProgress 2s ease-in-out infinite; /* Customize the animation */
}
```

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

## License

This Laravel Menu Manager is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
