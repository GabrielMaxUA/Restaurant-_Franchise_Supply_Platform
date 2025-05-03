<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router; // Add this import
use App\Http\Middleware\CheckRole; // Add this import

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('role', CheckRole::class);
        
        \Illuminate\Http\Request::macro('isValidSize', function() {
          return true; // Override the size validation
      });
    }
}