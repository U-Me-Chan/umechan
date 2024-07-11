<?php

namespace PK\Posts\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\IPostRepository;

final class GetThreadList
{
    public function __construct(
        private IPostRepository $post_repo
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        $limit  = $req->getParams('limit') ? $req->getParams('limit') : 20;
        $offset = $req->getParams('offset') ? $req->getParams('offset') : 0;

        $tags = explode('+', $vars['tags']);

        list($posts, $count) = $this->post_repo->findMany(['board_tags' => $tags, 'limit' => $limit, 'offset' => $offset, 'parent_id' => null]);

        return new Response(['count' => $count, 'posts' => $posts]);
    }
}
