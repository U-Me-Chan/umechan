<?php

namespace PK\Posts\Controllers;

use InvalidArgumentException;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\Posts\Exceptions\ThreadNotFoundException;
use PK\Posts\Services\PostFacade;

final class DeletePost
{
    public function __construct(
        private PostFacade $post_facade,
        private string $key
    ) {
    }

    public function __invoke(Request $req, array $vars): JsonResponse
    {
        if ($req->getHeaders('HTTP_KEY') == null) {
            return new JsonResponse([], 401)
                ->setException(new InvalidArgumentException("Не задан мастер-ключ"));
        }

        if ($req->getHeaders('HTTP_KEY') !== $this->key) {
            return new JsonResponse([], 401)
                ->setException(new InvalidArgumentException("Неверный мастер-ключ"));
        }

        $reason = $req->getParams('reason', 'Не указан');

        $id = $vars['id'];

        try {
            $this->post_facade->deletePostByOwnerChan($id, $reason);
            return new JsonResponse([], 204);
        } catch (ThreadNotFoundException $e) {
            return new JsonResponse([], 404)->setException($e);
        }
    }
}
