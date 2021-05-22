<?php

namespace GromIT\RoutesBrowser\Actions;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use October\Rain\Support\Str;

class ListRoutes
{
    /**
     * @var \GromIT\RoutesBrowser\Actions\GetRouteDetails
     */
    private $loadRouteDetails;

    public function __construct(GetRouteDetails $loadRouteDetails)
    {
        $this->loadRouteDetails = $loadRouteDetails;
    }

    /**
     * @return \Illuminate\Support\Collection|\GromIT\RoutesBrowser\Dto\RouteDetails[]
     */
    public function execute(): Collection
    {
        return collect(\Route::getRoutes())
            ->map(function (Route $route) {
                if ($this->isSystemRoute($route)) {
                    return null;
                }

                $routes = [];

                foreach ($route->methods() as $method) {
                    if (!in_array($method, config('gromit.routesbrowser::routes.show_methods'), true)) {
                        continue;
                    }

                    $routes[] = $this->loadRouteDetails->execute($route, $method);
                }

                return $routes;
            })
            ->filter(function ($data) {
                return !empty($data);
            })
            ->flatten()
            ->sortBy('uri');
    }

    private function isSystemRoute(Route $route): bool
    {
        return Str::startsWith($route->uri(), [
            'backend',
            'combine',
            'resize',
            '{slug?}'
        ]);
    }

}
