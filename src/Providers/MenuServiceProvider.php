<?php

namespace NguyenHuy\Menu\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use NguyenHuy\Menu\WMenu;
use NguyenHuy\Menu\Repositories\MenuRepository;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Load helpers first to ensure they're available for routes and views
        $this->loadHelpers();
        
        $this->registerRoutes();
        $this->registerViews();
        $this->registerPublishables();
        $this->registerAssets();
        $this->registerTranslations();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();
        $this->mergeConfig();
    }
    
    /**
     * Load helper functions
     * 
     * @return void
     */
    protected function loadHelpers()
    {
        require_once __DIR__ . '/../helpers.php';
    }

    /**
     * Register route files
     * 
     * @return void
     */
    protected function registerRoutes(): void
    {
        if (!$this->app->routesAreCached()) {
            require __DIR__ . '/../../routes/web.php';
        }
    }

    /**
     * Register views
     * 
     * @return void
     */
    protected function registerViews(): void
    {
        // Register package views path - this allows views to be loaded directly from the package
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'nguyendachuy-menu');
        
        // Register the fallback view paths for the package
        // This allows Laravel to check the published views first, then fall back to the package views
        $this->app['view']->addNamespace('nguyendachuy-menu', [
            resource_path('views/vendor/nguyendachuy-menu'),
            __DIR__ . '/../../resources/views',
        ]);
    }

    /**
     * Register assets
     * 
     * @return void
     */
    protected function registerAssets(): void
    {
        // Register the package assets route
        Route::get('/vendor/nguyendachuy-menu/{file}', function ($file) {
            $path = __DIR__ . '/../../public/' . $file;
            
            if (file_exists($path)) {
                // Determine the MIME type based on file extension
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                $contentType = [
                    'js' => 'application/javascript',
                    'css' => 'text/css',
                    'png' => 'image/png',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'svg' => 'image/svg+xml',
                ][$extension] ?? 'text/plain';
                
                return response()->file($path, ['Content-Type' => $contentType]);
            }
            
            abort(404);
        })->middleware('web')->name('nguyendachuy-menu.asset');
    }

    /**
     * Register publishable assets
     * 
     * @return void
     */
    protected function registerPublishables(): void
    {
        // Config
        $this->publishes([
            __DIR__ . '/../../config/menu.php' => config_path('menu.php'),
        ], 'laravel-menu-config');

        // Views
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/nguyendachuy-menu'),
        ], 'laravel-menu-view');

        // Assets
        $this->publishes([
            __DIR__ . '/../../public' => public_path('vendor/nguyendachuy-menu'),
        ], 'laravel-menu-public');

        // Migrations
        $this->publishMigrations();
    }

    /**
     * Register service container bindings
     * 
     * @return void
     */
    protected function registerBindings(): void
    {
        // Bind the menu class
        $this->app->bind('nguyendachuy-menu', function ($app) {
            return new WMenu();
        });

        // Bind the menu repository as singleton
        $this->app->singleton(MenuRepository::class, function ($app) {
            return new MenuRepository();
        });

        // Register controller namespace
        $this->app->make('NguyenHuy\Menu\Http\Controllers\MenuController');
    }

    /**
     * Merge configuration
     * 
     * @return void
     */
    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/menu.php',
            'menu'
        );
    }
    
    /**
     * Register helper functions
     * 
     * @return void
     */
    protected function registerHelpers(): void
    {
        // Legacy helpers registration - kept for backward compatibility
    }
    
    /**
     * Register translations
     * 
     * @return void
     */
    protected function registerTranslations(): void
    {
        // Load translations from the package
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'menu');
        
        // Publish translations
        $this->publishes([
            __DIR__ . '/../../resources/lang' => resource_path('lang/vendor/menu'),
        ], 'laravel-menu-translations');
    }

    /**
     * Publish migration files
     * 
     * @return void
     */
    protected function publishMigrations(): void
    {
        $migrations = [
            '2017_08_11_073824_create_menus_wp_table.php',
            '2017_08_11_074006_create_menu_items_wp_table.php',
            '2019_01_05_293551_add-role-id-to-menu-items-table.php',
            '2022_07_06_000123_add_class_to_menu_table.php',
            '2023_10_20_000001_add_indexes_to_menus_table.php',
            '2023_10_20_000002_add_indexes_to_menu_items_table.php'
        ];

        $publishMigrations = [];
        foreach ($migrations as $migration) {
            $source = __DIR__ . '/../../database/migrations/' . $migration;
            $destination = $this->getMigrationFileName($migration);
            
            if (file_exists($source)) {
                $publishMigrations[$source] = $destination;
            }
        }
        
        $this->publishes($publishMigrations, 'laravel-menu-migrations');
    }

    /**
     * Get the migration file name with timestamp
     * 
     * @param string $migrationFileName
     * @return string
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        // Check if migration already exists
        if ($this->migrationExists($migrationFileName)) {
            return database_path('migrations/' . $this->getExistingMigrationFileName($migrationFileName));
        }

        // If not, return with current timestamp
        return database_path('migrations/' . $migrationFileName);
    }

    /**
     * Get existing migration filename
     * 
     * @param string $migrationName
     * @return string|null
     */
    protected function getExistingMigrationFileName(string $migrationName): ?string
    {
        $files = scandir(database_path('migrations/'));
        
        foreach ($files as $file) {
            if (strpos($file, substr($migrationName, 18)) !== false) {
                return $file;
            }
        }
        
        return null;
    }

    /**
     * Check if migration exists
     * 
     * @param string $migrationName
     * @return bool
     */
    protected function migrationExists(string $migrationName): bool
    {
        $path = database_path('migrations/');
        
        if (!file_exists($path)) {
            return false;
        }
        
        $files = scandir($path);
        
        foreach ($files as $file) {
            if (strpos($file, substr($migrationName, 18)) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
