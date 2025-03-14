<?php

namespace Ridouchire\RadioScheduler\Http\Controllers;

use React\Http\Message\Response;
use OpenApi\Generator;

final class GetOpenApiSpecifitation
{
    public function __invoke(): Response
    {
        $openapi = Generator::scan([__DIR__ . '/../../']);

        return new Response(
            Response::STATUS_OK,
            [
                'Content-type' => 'application/json'
            ],
            $openapi->toJson()
        );
    }
}
