<?php

namespace Workdo\TaxOptimizer\Listeners;

use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Support\Facades\Gate;

class CompanyMenuListener
{
    public function handle(BuildingMenu $event)
    {
        // Verifica se o módulo TaxOptimizer está ativo e se o usuário tem permissão
        if (module_is_active('TaxOptimizer') && Gate::allows('taxoptimizer manage')) {
            $event->menu->add([
                'text' => 'Otimização Tributária',
                'url'  => route('taxoptimizer.index'),
                'icon' => 'fas fa-chart-line',
                'can'  => 'taxoptimizer manage',
                'order' => 1000, // Colocar no final do menu
            ]);
        }
    }
}
