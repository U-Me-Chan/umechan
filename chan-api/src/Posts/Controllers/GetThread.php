<?php

namespace PK\Posts\Controllers;

use PK\Boards\BoardStorage;
use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\PostStorage;
use PK\Posts\Post;

final class GetThread
{
    public function __construct(
        private PostStorage $storage,
        private BoardStorage $board_storage
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        /** @var int */
        $id = $vars['id'];

        try {
            /** @var Post */
            $post = $this->storage->findById($id);
        } catch (\OutOfBoundsException $e) {
            return new Response([], 404)->setException($e);
        }

        return new Response(['thread_data' => $post, 'boards' => $this->board_storage->find()]);
    }
}
