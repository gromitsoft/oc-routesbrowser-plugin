<?php

namespace GromIT\RoutesBrowser\Actions;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

class FindRoute
{
    public function execute(string $method, string $uri): ?Route
    {
        $route = $this->findRoute($method, $uri);

        return $route ?? null;
    }

    private function findRoute(string $method, string $uri): ?Route
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return collect(RouteFacade::getRoutes())
            ->first(function (Route $route) use ($method, $uri) {
                return $route->uri() === $uri && in_array($method, $route->methods(), true);
            });
    }
}
