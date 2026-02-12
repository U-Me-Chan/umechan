<?php

namespace PK\Posts\Controllers;

use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\Posts\Exceptions\ThreadNotFoundException;
use PK\Posts\Services\PostService;

final class GetThreadFileList
{
    public function __construct(
        private PostService $post_service
    ) {
    }

    public function __invoke(Request $req, array $vars): JsonResponse
    {
        try {
            $result = $this->post_service->getThreadFiles($vars['id']);

            return new JsonResponse(['files' => $result], 200);
        } catch (ThreadNotFoundException $e) {
            return new JsonResponse([], 404)->setException($e);
        }
    }
}
