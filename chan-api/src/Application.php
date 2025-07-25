<?php

namespace PK;

use Pimple\Container;
use OpenApi\Attributes as OA;
use PK\Http\Response;
use PK\Http\Request;

#[OA\Info(
    version: '2.1.0',
    title: 'Pissykaka Public API'
)]
#[OA\License(name: 'MIT', identifier: 'MIT')]
#[OA\Server(url: 'https://scheoble.xyz/', description: 'production server')]
class Application extends Container
{
    public static Application $app;

    public function __construct(
        private RequestHandler $request_handler,
        array $config,
    ) {
        self::$app = $this;
        self::$app['config'] = $config;
        self::$app = $this;
    }

    public function run(Request $request): void
    {
        /** @var Response */
        $res = $this->request_handler->handle($request);

        $this->send($res);
    }

    private function send(Response $res): never
    {
        if (!empty($res->getHeaders())) {
            foreach ($res->getHeaders() as $header) {
                header($header);
            }
        }

        http_response_code($res->getCode());
        echo $res->getBody();

        exit(0);
    }
}
