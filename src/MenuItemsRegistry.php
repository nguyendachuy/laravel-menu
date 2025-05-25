<?php

namespace NguyenHuy\Menu;

class MenuItemsRegistry
{
    /**
     * Registered menu sections
     * 
     * @var array
     */
    protected static $sections = [];
    
    /**
     * Register a new menu section with items
     * 
     * @param string $name Section name
     * @param array $items Array of menu items
     * @param bool $show Whether to show expanded by default
     * @return void
     */
    public static function registerSection(string $name, array $items, bool $show = false): void
    {
        self::$sections[$name] = [
            'name' => $name,
            'items' => $items,
            'show' => $show
        ];
    }
    
    /**
     * Get all registered sections
     * 
     * @return array
     */
    public static function getAllSections(): array
    {
        return self::$sections;
    }
    
    /**
     * Get a specific section by name
     * 
     * @param string $name
     * @return array|null
     */
    public static function getSection(string $name): ?array
    {
        return self::$sections[$name] ?? null;
    }
    
    /**
     * Check if a section exists
     * 
     * @param string $name
     * @return bool
     */
    public static function hasSection(string $name): bool
    {
        return isset(self::$sections[$name]);
    }
    
    /**
     * Add items to an existing section
     * 
     * @param string $name
     * @param array $items
     * @return void
     */
    public static function addItemsToSection(string $name, array $items): void
    {
        if (self::hasSection($name)) {
            self::$sections[$name]['items'] = array_merge(self::$sections[$name]['items'], $items);
        } else {
            self::registerSection($name, $items);
        }
    }
}
