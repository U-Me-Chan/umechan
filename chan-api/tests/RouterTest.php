<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PK\RequestHandlers\Router;
use PK\Http\Response;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        $this->router->addRoute('GET', '/test', fn() => new JsonResponse([], 200));
        $this->router->addRoute('GET', '/error', fn() => throw new Exception());
    }

    #[Test]
    #[DataProvider('dataProvider')]
    public function attemptHandle(string $method, string $path, int $code): void
    {
        $server['REQUEST_METHOD'] = $method;
        $server['REQUEST_URI']    = $path;

        /** @var Response */
        $res = $this->router->handle(new Request($server));

        $this->assertEquals($code, $res->getCode());
    }

    public static function dataProvider(): array
    {
        return [
            ['GET', '/api/404', 404],
            ['GET', '/api/test', 200],
            ['POST', '/api/test', 405],
            ['GET', '/api/error', 500]
        ];
    }
}
