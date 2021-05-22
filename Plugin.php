<?php namespace GromIT\RoutesBrowser;

use Backend;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'RoutesBrowser',
            'description' => 'Plugin for browse routes',
            'author'      => 'GromIT',
            'icon'        => 'icon-question'
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation(): array
    {
        return [
            'routes' => [
                'label'       => 'Routes',
                'url'         => Backend::url('gromit/routesbrowser'),
                'icon'        => 'icon-book',
                'order'       => 500,
            ],
        ];
    }
}
