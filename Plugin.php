<?php namespace GromIT\RoutesBrowser;

use Backend;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'RoutesBrowser',
            'description' => 'Plugin for browse routes',
            'author'      => 'GromIT',
            'icon'        => 'icon-question'
        ];
    }

    public function registerPermissions(): array
    {
        return [
            'gromit.routesbrowser.view' => [
                'label' => 'View routes',
                'tab'   => 'RoutesBrowser',
            ]
        ];
    }

    public function registerNavigation(): array
    {
        return [
            'routes' => [
                'label'       => 'Routes',
                'url'         => Backend::url('gromit/routesbrowser'),
                'icon'        => 'icon-book',
                'order'       => 500,
                'permissions' => ['gromit.routesbrowser.view']
            ],
        ];
    }
}
