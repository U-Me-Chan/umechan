<?php

namespace Ridouchire\RadioScheduler\Http;

use OpenApi\Attributes as OA;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher\GroupCountBased as RouteDispatcher;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

#[OA\Info(
    version: '1.0.0',
    title: 'Radio Scheduler Public API',

)]
#[OA\License(name: 'MIT', identifier: 'MIT')]
#[OA\Server(url: 'https://scheoble.xyz/', description: 'production server')]
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
            case RouteDispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                try {
                    /** @var Response */
                    $res = call_user_func($handler, $req, $vars);

                    return $res;
                } catch (\Throwable) {
                    /** @var Response */
                    $res = Response::json([]);
                    $res = $res->withStatus(Response::STATUS_INTERNAL_SERVER_ERROR);

                    return $res;
                }
            default:
                /** @var Response */
                $res = Response::json([]);
                $res = $res->withStatus(Response::STATUS_NOT_FOUND);

                return $res;
        }
    }

    public function addRoute(string $method, string $path, callable $callback): void
    {
        $this->route_collector->addRoute($method, $path, $callback);
    }
}
