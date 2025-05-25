<?php

if (!function_exists('nguyendachuy_menu_asset')) {
    /**
     * Generate an asset path for the package.
     *
     * @param  string  $path
     * @return string
     */
    function nguyendachuy_menu_asset($path)
    {
        // Direct implementation without relying on app container
        if (file_exists(public_path("vendor/nguyendachuy-menu/{$path}"))) {
            return asset("vendor/nguyendachuy-menu/{$path}");
        }
        
        // Fall back to package route
        $routeExists = function_exists('route') && 
                       app('router')->has('nguyendachuy-menu.asset');
        
        if ($routeExists) {
            return route('nguyendachuy-menu.asset', ['file' => $path]);
        }
        
        // Last resort fallback
        return url("/vendor/nguyendachuy-menu/{$path}");
    }
}
