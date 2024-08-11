<?php

namespace PK\Posts\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\IPostRepository;
use PK\Posts\Post\Post;

final class GetThread
{
    public function __construct(
        private IPostRepository $post_repo
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        $id = $vars['id'];

        try {
            /** @var Post */
            $thread = $this->post_repo->findOne(['id' => $id]);
        } catch (\OutOfBoundsException) {
            return new Response([], 404);
        }

        return new Response(['thread_data' => $thread]);
    }
}
