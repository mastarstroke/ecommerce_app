<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Blade;
use App\Services\CartService;

class AppServiceProvider extends ServiceProvider
{


    public function register(): void
    {
        Sanctum::ignoreMigrations();
        // Bind CartService as a singleton to prevent multiple instances
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService();
        });
    }

    public function boot(): void
    {
        // Admin blade directive
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->is_admin;
        });
        
        // Customer blade directive
        Blade::if('customer', function () {
            return auth()->check() && !auth()->user()->is_admin;
        });
    }
}