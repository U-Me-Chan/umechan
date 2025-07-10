<?php

namespace PK;

use Pimple\Container;
use OpenApi\Attributes as OA;
use PK\Http\Response;

#[OA\Info(
    version: '2.1.0',
    title: 'Pissykaka Public API'
)]
#[OA\License(name: 'MIT', identifier: 'MIT')]
#[OA\Server(url: 'https://scheoble.xyz/', description: 'production server')]
class Application extends Container
{
    public static Application $app;

    public function __construct(array $config)
    {
        self::$app = $this;
        self::$app['config'] = $config;
        self::$app = $this;
    }

    public function run(): void
    {
        /** @var Response */
        $res = $this['router']->handle($this['request']);

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
