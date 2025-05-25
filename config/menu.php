<?php

/**
 * Configuration file for the Laravel Menu package.
 *
 * This file contains various configuration options for the package.
 * You can customize the middleware, table names, route prefix, and more.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Localization Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable localization support for the menu builder.
    | When enabled, menu items can be translated to different languages.
    |
    */
    'localization' => [
        'enabled' => true,           // Enable or disable localization support
        'default_locale' => 'en',    // Default locale when no locale is specified
        'available_locales' => [     // Available locales for the menu builder
            'en' => 'English',
            'vi' => 'Tiếng Việt',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Middleware
    |--------------------------------------------------------------------------
    |
    | You can add your own middleware here. This middleware will be applied
    | to all routes registered by the package.
    |
    */
    'middleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configure database table prefix and table names.
    |
    */
    'table_prefix' => 'admin_',
    'table_name_menus' => 'menus',
    'table_name_items' => 'menu_items',

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Set the route prefix for all menu-related routes.
    |
    */
    'route_prefix' => 'nguyendachuy',

    /*
    |--------------------------------------------------------------------------
    | Role Access Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable role-based permissions for menu items.
    | When enabled, specify the roles table details.
    |
    */
    'use_roles' => false,
    'roles_table' => 'roles',
    'roles_pk' => 'id',        // Primary key of the roles table
    'roles_title_field' => 'name', // Display name (field) of the roles table

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for menu items to improve performance.
    |
    */
    'cache_enabled' => false,  // Enable or disable menu caching
    'cache_key_prefix' => 'menu', // Prefix for cache keys
    'cache_ttl' => 60,         // Cache time-to-live in minutes
];
