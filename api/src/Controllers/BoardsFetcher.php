<?php

namespace PK\Controllers;

use Medoo\Medoo;
use PK\Http\Request;
use PK\Http\Response;
use PK\Boards\IBoardRepository;

class BoardsFetcher
{
    public function __construct(
        private IBoardRepository $board_repo,
        private Medoo $db
    ) {
    }

    /**
     * Обрабатывает запрос списка досок и последних постов
     *
     * @param Request $req
     *
     * @return Response
     */
    public function __invoke(Request $req): Response
    {
        list($boards,) = $this->board_repo->findMany();

        $results['boards'] = $boards;

        $exclude_tags = $req->getParams('exclude_tags') ? $req->getParams('exclude_tags') : ['und', 'fap'];
        $limit        = $req->getParams('limit') ? $req->getParams('limit') : 20;
        $offset       = $req->getParams('offset') ? $req->getParams('offset') : 0;

        $conditions = [
            'AND' => [
                'boards.tag[!]' => $exclude_tags
            ],
            'LIMIT' => [$offset, $limit],
            'ORDER' => ['posts.timestamp' => 'DESC']
        ];

        if ($req->getParams('query')) {
            $conditions['AND']['OR'] = [
                'posts.subject[~]' => "%{$req->getParams('query')}%",
                'posts.message[~]' => "%{$req->getParams('query')}%"
            ];
        }

        $results['posts'] = array_map(function ($post) {
            $post['is_verify'] = ($post['is_verify'] === 'yes' ? true : false);
            return $post;
          },
          $this->db->select(
              'posts',
              [
                  '[>]boards' => [
                      'board_id' => 'id'
                  ]
              ],
              [
                  'posts.id',
                  'posts.poster',
                  'posts.subject',
                  'posts.message',
                  'posts.timestamp',
                  'posts.parent_id',
                  'posts.is_verify',
                  'boards.tag'
              ],
              $conditions
          )
        );

        return new Response($results, 200);
    }
}
