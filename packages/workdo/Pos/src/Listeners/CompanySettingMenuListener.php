<?php

namespace Workdo\PDV\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingMenuEvent $event): void
    {
        $module = 'PDV';
        $menu = $event->menu;
        $menu->add([
            'title' => __('PDV Settings'),
            'name' => 'pdv-setting',
            'order' => 180,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'pdv-sidenav',
            'module' => $module,
            'permission' => 'pdv setting manage'
        ]);

        $menu->add([
            'title' => __('PDV Print Settings'),
            'name' => 'pdv-print-setting',
            'order' => 200,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'pdv-print-sidenav',
            'module' => $module,
            'permission' => 'pdv setting manage'
        ]);
    }
}
