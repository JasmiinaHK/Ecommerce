<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;

Route::get('/debug/admin-routes', function() {
    $routes = [];
    
    // Check if admin routes are loaded
    try {
        $adminRoutes = Route::getRoutes()->getRoutes();
        
        foreach ($adminRoutes as $route) {
            if (strpos($route->uri(), 'admin') !== false) {
                $routes[] = [
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'methods' => $route->methods(),
                ];
            }
        }
        
        return response()->json($routes);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
});

// Test route to check if the file is being loaded
Route::get('/debug/test', function() {
    return 'Debug route is working!';
});
