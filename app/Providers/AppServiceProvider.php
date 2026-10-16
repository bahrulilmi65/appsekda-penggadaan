<?php

namespace App\Providers;

use App\Models\Cart;
use App\Policies\CartPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Cart::class => CartPolicy::class,
    ];

    public function register(): void
    {
        // Bind services
        $this->app->singleton(\App\Services\AvailabilityService::class);
        $this->app->singleton(\App\Services\InventoryService::class);
    }

    public function boot(): void
    {
        // Register policies
        Gate::policy(Cart::class, CartPolicy::class);

        // Set locale for Carbon
        \Carbon\Carbon::setLocale('id');

        // Gunakan Bootstrap 5 sebagai tampilan pagination default
        Paginator::useBootstrapFive();
    }
}
