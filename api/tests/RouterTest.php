<?php

use PHPUnit\Framework\TestCase;
use PK\Router;
use PK\Http\Response;
use PK\Http\Request;
use PK\Exceptions\Http\NotFound;

class RouterTest extends TestCase
{
    private $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        $this->router->addRoute('GET', '/test', function (Request $req) {
            return new Response([], 200);
        });
    }

    public function testHandleNotFound()
    {
        $server['REQUEST_METHOD'] = 'GET';
        $server['REQUEST_URI']    = '/404';

        $this->assertEquals((new Response([], 404))->setException(new NotFound()), $this->router->handle(new Request($server)));
    }

    public function testHandleFound()
    {
        $server['REQUEST_METHOD'] = 'GET';
        $server['REQUEST_URI']    = '/api/test';

        /** @var Response */
        $res = $this->router->handle(new Request($server));

        $this->assertEquals(200, $res->getCode());
    }
}
