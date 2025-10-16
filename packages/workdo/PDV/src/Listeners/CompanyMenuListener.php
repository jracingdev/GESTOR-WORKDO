<?php

namespace Workdo\PDV\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'PDV';
        $menu = $event->menu;
        $menu->add([
            'category' => 'General',
            'title' => __('PDV Dashboard'),
            'icon' => '',
            'name' => 'pdv-dashboard',
            'parent' => 'dashboard',
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'pos.dashboard',
            'module' => $module,
            'permission' => 'pdv dashboard manage'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('PDV'),
            'icon' => 'grid-dots',
            'name' => 'pdv',
            'parent' => null,
            'order' => 475,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'pdv manage'
        ]);


        $menu->add([
            'category' => 'Sales',
            'title' => __('Add PDV'),
            'icon' => '',
            'name' => 'add-pdv',
            'parent' => 'pdv',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'pdv.index',
            'module' => $module,
            'permission' => 'pdv add manage'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('PDV Order'),
            'icon' => '',
            'name' => 'pdv-order',
            'parent' => 'pdv',
            'order' => 25,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'pdv.report',
            'module' => $module,
            'permission' => 'pdv order manage'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('Print Barcode'),
            'icon' => '',
            'name' => 'pdv-print-barcode',
            'parent' => 'pdv',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'pdv.barcode',
            'module' => $module,
            'permission' => 'print_barcode manage'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('Report'),
            'icon' => '',
            'name' => 'pdv-reports',
            'parent' => 'pdv',
            'order' => 35,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'report pdv'
        ]);


        $menu->add([
            'category' => 'Sales',
            'title' => __('PDV Daily/Monthly Report'),
            'icon' => '',
            'name' => 'pdv-report',
            'parent' => 'pdv-reports',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'report.daily.pdv',
            'module' => $module,
            'permission' => 'report pdv'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('PDV VS Purchase Report'),
            'icon' => '',
            'name' => 'pdv-vs-purchase-report',
            'parent' => 'pdv-reports',
            'order' => 25,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'report.pdv.vs.purchase',
            'module' => $module,
            'permission' => 'report pdv vs expense'
        ]);
    }
}
