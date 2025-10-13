<?php

namespace Workdo\FiscalBR\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // Eventos futuros para integração com outros módulos
        // 'Workdo\Account\Events\InvoiceCreated' => [
        //     'Workdo\FiscalBR\Listeners\CreateNFeFromInvoice',
        // ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}

