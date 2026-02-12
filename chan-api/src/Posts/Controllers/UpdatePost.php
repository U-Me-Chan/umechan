<?php

namespace PK\Posts\Controllers;

use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\Posts\Exceptions\ThreadNotFoundException;
use PK\Posts\Services\PostService;

final class UpdatePost
{
    public function __construct(
        private PostService $post_service
    ) {
    }

    public function __invoke(Request $request, array $vars): JsonResponse
    {
        try {
            $this->post_service->updatePostByAuthor($vars['id'], $request->getParams());
        } catch (ThreadNotFoundException $e) {
            return new JsonResponse([], 404)->setException($e);
        }

        return new JsonResponse([], 200);
    }
}
