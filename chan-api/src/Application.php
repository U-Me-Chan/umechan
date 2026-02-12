<?php

namespace PK;

use OpenApi\Attributes as OA;
use PK\Http\Response;
use PK\Http\Request;
use PK\Services\HookService;
use PK\Utils\ApplicationHook;

#[OA\Info(
    version: '2.1.0',
    title: 'Pissykaka Public API'
)]
#[OA\License(name: 'MIT', identifier: 'MIT')]
#[OA\Server(url: 'https://scheoble.xyz/', description: 'production server')]
class Application
{
    public function __construct(
        private RequestHandler $request_handler,
        private HookService $hook_service
    ) {
    }

    public function run(Request $request): void
    {
        $this->hook_service->setHook(ApplicationHook::before_run, [$request]);

        /** @var Response */
        $res = $this->request_handler->handle($request);

        $this->hook_service->setHook(ApplicationHook::after_run, [$request, $res]);

        $this->send($res);
    }

    private function send(Response $res): void
    {
        $this->hook_service->setHook(ApplicationHook::before_send, [$res]);

        ob_start();

        if (!empty($res->getHeaders())) {
            foreach ($res->getHeaders() as $header) {
                header($header);
            }
        }

        http_response_code($res->getCode());
        echo $res->getBody();

        ob_end_flush();

        $this->hook_service->setHook(ApplicationHook::after_send);

        exit(0);
    }
}
