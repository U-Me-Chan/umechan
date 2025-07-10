<?php

namespace PK\OpenApi\Controllers;

use OpenApi\Generator;
use PK\Http\Responses\JsonResponse;

final class GetOpenApiSpecification
{
    public function __construct(
        private Generator $generator
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $openapi = $this->generator->generate([__DIR__ . '/../../']);

        $res = new JsonResponse([], 200);
        $res->setPreformattedJson($openapi->toJson());

        return $res;
    }
}
