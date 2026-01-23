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
        // Força HTTPS apenas em produção
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        
        // Configura paginação bootstrap
        Paginator::useBootstrapFive();
    }
}
