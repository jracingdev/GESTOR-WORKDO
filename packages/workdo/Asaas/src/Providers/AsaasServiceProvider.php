<?php

namespace Workdo\Asaas\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AsaasServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'asaas');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'asaas');

        // Adicionar o menu de configurações
        $this->app->make('events')->listen(
            \App\Events\CompanySettingMenuEvent::class,
            \Workdo\Asaas\Listeners\CompanySettingMenuListener::class
        );
    }

    public function register()
    {
        //
    }
}
