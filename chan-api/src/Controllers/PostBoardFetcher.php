<?php

namespace PK\Controllers;

use OutOfBoundsException;
use PK\Http\Request;
use PK\Http\Response;
use PK\Boards\Board\Board;
use PK\Boards\BoardStorage;
use PK\Posts\PostStorage;

class PostBoardFetcher
{
    public function __construct(
        private BoardStorage $board_repo,
        private PostStorage $post_repo
    ) {
    }

    /**
     * Обрабатывает запрос списка тредов доски
     *
     * @param Request $req
     * @param array   $vars
     *
     * @return Response
     */
    public function __invoke(Request $req, array $vars): Response
    {
        /** @var string */
        $board_tag = $vars['tag'];

        try {
            /** @var Board */
            $board = $this->board_repo->findByTag($board_tag);
        } catch (OutOfBoundsException $e) {
            return (new Response([], 404))->setException($e);
        }

        $results['board_data'] = $board->toArray();
        $results['board_data']['threads'] = [];

        /** @var int */
        $limit = $req->getParams('limit') ? $req->getParams('limit') : 20;

        /** @var int */
        $offset = $req->getParams('offset') ? $req->getParams('offset') : 0;

        list($posts, $count) = $this->post_repo->find($limit, $offset, [$board->tag]);

        $results['board_data']['threads'] = $posts;
        $results['board_data']['threads_count'] = $count;

        return new Response($results, 200);
    }
}
