<?php

namespace PK;

use Exception;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher\GroupCountBased as RouteDispatcher;
use PK\Http\Request;
use PK\Http\Response;
use PK\Http\Responses\JsonResponse;

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
                return new JsonResponse([], 404)->setException(new Exception('Not found'));
            case RouteDispatcher::METHOD_NOT_ALLOWED:
                return new JsonResponse([], 405)->setException(new Exception('Not allowed'));
            case RouteDispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                try {
                    return call_user_func($handler, $req, $vars);
                } catch (\Throwable $e) {
                    return (new JsonResponse([], 500))->setException($e);
                }
            default:
                return new JsonResponse([], 400)->setException(new Exception('Bad request'));
        }
    }

    public function addRoute(string $method, string $path, callable $callback): void
    {
        $this->route_collector->addRoute($method, self::PREFIX . $path, $callback);
    }
}
