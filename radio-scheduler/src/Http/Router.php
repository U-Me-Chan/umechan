<?php

namespace Ridouchire\RadioScheduler\Http;

use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher\GroupCountBased as RouteDispatcher;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class Router
{
    private RouteCollector $route_collector;

    public function __construct()
    {
        $this->route_collector = new RouteCollector(new RouteParser(), new DataGenerator());
    }

    public function __invoke(ServerRequestInterface $req): Response
    {
        $dispatcher = new RouteDispatcher($this->route_collector->getData());

        $routeInfo = $dispatcher->dispatch($req->getMethod(), $req->getUri()->getPath());

        switch ($routeInfo[0]) {
            case RouteDispatcher::NOT_FOUND:
            case RouteDispatcher::METHOD_NOT_ALLOWED:
                $res = Response::json([]);
                $res = $res->withStatus(Response::STATUS_NOT_FOUND);

                return $res;

                break;
            case RouteDispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                try {
                    return call_user_func($handler, $req, $vars);
                } catch (\Throwable) {
                    $res = Response::json([]);
                    $res = $res->withStatus(Response::STATUS_INTERNAL_SERVER_ERROR);

                    return $res;
                }

                break;
        }
    }

    public function addRoute(string $method, string $path, callable $callback): void
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException();
        }

        $this->route_collector->addRoute($method, $path, $callback);
    }
}
