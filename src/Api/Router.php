<?php

namespace Api;

class Router
{
    private array $routes = [];

    public function addRoute(string $method, string $path, string $handler): void
    {
        $this->routes[] = ['method' => $method, 'path' => $path, 'handler' => $handler];
    }

    public function dispatch(string $method, string $path): array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            $pattern = str_replace('\*', '([^/]+)', preg_quote($route['path'], '/'));
            if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                array_shift($matches);
                list($class, $action) = explode('@', $route['handler']);
                $controller = new $class();
                return $controller->$action($matches);
            }
        }
        return ['error' => 'Not found', 'code' => 404];
    }

    public static function register(): self
    {
        $router = new self();

        // Auth
        $router->addRoute('POST', '/auth/register', 'Api\Controller\AuthController@register');
        $router->addRoute('POST', '/auth/login', 'Api\Controller\AuthController@login');
        $router->addRoute('POST', '/auth/logout', 'Api\Controller\AuthController@logout');
        $router->addRoute('GET', '/auth/me', 'Api\Controller\AuthController@me');

        // Planet
        $router->addRoute('GET', '/planet', 'Api\Controller\PlanetController@get');
        $router->addRoute('PUT', '/planet/build', 'Api\Controller\PlanetController@buildBuilding');
        $router->addRoute('PUT', '/planet/research', 'Api\Controller\PlanetController@startResearch');
        $router->addRoute('GET', '/planet/building-types', 'Api\Controller\PlanetController@getBuildingTypes');
        $router->addRoute('GET', '/planet/research-types', 'Api\Controller\PlanetController@getResearchTypes');

        // Shipyard
        $router->addRoute('GET', '/shipyard', 'Api\Controller\ShipyardController@getShips');
        $router->addRoute('PUT', '/shipyard/build', 'Api\Controller\ShipyardController@buildShips');
        $router->addRoute('GET', '/shipyard/ship-types', 'Api\Controller\ShipyardController@getShipTypes');

        // Fleet
        $router->addRoute('POST', '/fleet/send', 'Api\Controller\FleetController@sendFleet');
        $router->addRoute('GET', '/fleet/active', 'Api\Controller\FleetController@getActiveFleets');
        $router->addRoute('PUT', '/fleet/orbit/deploy', 'Api\Controller\FleetController@deployToOrbit');
        $router->addRoute('PUT', '/fleet/orbit/recall', 'Api\Controller\FleetController@recallFromOrbit');
        $router->addRoute('GET', '/fleet/orbital-defense', 'Api\Controller\FleetController@getOrbitalDefense');

        // Flood
        $router->addRoute('GET', '/flood/state', 'Api\Controller\FloodController@getState');
        $router->addRoute('GET', '/flood/events', 'Api\Controller\FloodController@getEvents');

        return $router;
    }
}
