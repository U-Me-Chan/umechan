<?php

namespace PK\Base\Controllers;

use PK\Http\Request;
use PK\Http\Responses\JsonResponse;

final class GetDebugRequestData
{
    public function __invoke(Request $req): JsonResponse
    {
        return new JsonResponse(
            [
                'headers' => $req->getHeaders(),
                'hash'    => $req->getHash(),
                'method'  => $req->getMethod(),
                'params'  => $req->getParams(),
                'path'    => $req->getPath()
            ],
            200
        );
    }
}
