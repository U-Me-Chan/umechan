<?php

namespace PK;

use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher\GroupCountBased as RouteDispatcher;
use PK\Exceptions\Http\NotFound;
use PK\Http\Request;
use PK\Http\Response;

class Router
{
    private const PREFIX = '/api';

    private RouteCollector $route_collector;

    public function __construct()
    {
        $this->route_collector = new RouteCollector(new RouteParser(), new DataGenerator());
    }

    public function handle(Request $req): Response
    {
        $dispatcher = new RouteDispatcher($this->route_collector->getData());

        $routeInfo = $dispatcher->dispatch($req->getMethod(), $req->getPath());

        switch ($routeInfo[0]) {
            case RouteDispatcher::NOT_FOUND:
                return (new Response([], 404))->setException(new NotFound());
            case RouteDispatcher::METHOD_NOT_ALLOWED:
                return new Response([], 405);
            case RouteDispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                try {
                    return call_user_func($handler, $req, $vars);
                } catch (\Throwable $e) {
                    return (new Response([], 500))->setException($e);
                }
            default:
                return new Response([], 400);
        }
    }

    public function addRoute(string $method, string $path, callable $callback): void
    {
        $this->route_collector->addRoute($method, self::PREFIX . $path, $callback);
    }
}
