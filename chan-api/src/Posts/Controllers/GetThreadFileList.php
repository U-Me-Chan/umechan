<?php

namespace PK\Posts\Controllers;

use OutOfBoundsException;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\Posts\Services\PostFacade;
use PK\Posts\Post;

final class GetThreadFileList
{
    public function __construct(
        private PostFacade $post_facade
    ) {
    }

    public function __invoke(Request $req, array $vars): JsonResponse
    {
        try {
            /** @var Post */
            list($thread) = $this->post_facade->getThread($vars['id'], no_board_list: true);

            $result = $thread->getMedia();

            /** @var Post $post */
            foreach ($thread->replies as $post) {
                $result = array_merge(array_values($post->getMedia()), $result);
            }

            return new JsonResponse(['files' => $result], 200);
        } catch (OutOfBoundsException $e) {
            return new JsonResponse([], 404)->setException($e);
        }
    }
}
