<?php

namespace PK\Pissykaka\Controllers;

use PK\Http\Request;
use PK\Http\Responses\JsonResponse;

final class GetClientRequest
{
    public function __invoke(Request $req): JsonResponse
    {
        return new JsonResponse([
            'params' => $req->getParams(),
            'headers' => $req->getHeaders()
        ]);
    }
}
