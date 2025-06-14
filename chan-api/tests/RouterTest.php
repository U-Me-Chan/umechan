<?php

use PHPUnit\Framework\TestCase;
use PK\Router;
use PK\Http\Response;
use PK\Http\Request;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        $this->router->addRoute('GET', '/test', fn() => new Response([], 200));
    }

    public function testHandleNotFound(): void
    {
        $server['REQUEST_METHOD'] = 'GET';
        $server['REQUEST_URI']    = '/404';

        $this->assertEquals(new Response([], 404), $this->router->handle(new Request($server)));
    }

    public function testHandleFound(): void
    {
        $server['REQUEST_METHOD'] = 'GET';
        $server['REQUEST_URI']    = '/api/test';

        /** @var Response */
        $res = $this->router->handle(new Request($server));

        $this->assertEquals(200, $res->getCode());
    }
}
