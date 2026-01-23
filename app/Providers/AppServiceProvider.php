<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Paginator::currentPathResolver(function () {
            return request()->url();
        });

         // Configura paginação bootstrap
        Paginator::useBootstrapFive();
        
        // Resolve problema de path na paginação
        Paginator::currentPathResolver(function () {
            return request()->getPathInfo();
        });
    }
}
