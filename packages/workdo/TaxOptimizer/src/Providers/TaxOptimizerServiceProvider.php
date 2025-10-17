<?php

namespace Workdo\TaxOptimizer\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Workdo\TaxOptimizer\Listeners\CompanyMenuListener;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class TaxOptimizerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'taxoptimizer');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Registro do Listener de Menu
        Event::listen(
            BuildingMenu::class,
            [CompanyMenuListener::class, 'handle']
        );
    }

    public function register()
    {
        //
    }
}
