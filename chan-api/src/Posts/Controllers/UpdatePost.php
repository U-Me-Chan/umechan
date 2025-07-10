<?php

namespace PK\Posts\Controllers;

use PK\Http\Request;
use PK\Http\Responses\JsonResponse;

final class UpdatePost
{
    public function __construct(
        private string $key
    ) {
    }

    public function __invoke(Request $req, array $vars): JsonResponse
    {
        if ($req->getHeaders('HTTP_KEY') == null) {
            return new JsonResponse([], 401);
        }

        if ($req->getHeaders('HTTP_KEY') !== $this->key) {
            return new JsonResponse([], 401);
        }

        // todo: post update functionality
        // todo: dont forget about event creation

        return new JsonResponse();
    }
}
