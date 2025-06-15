<?php

namespace PK\Feed\Controllers;

use Medoo\Medoo;
use PK\Http\Request;
use PK\Http\Response;
use PK\Boards\BoardStorage;

class BoardsFetcher
{
    public function __construct(
        private BoardStorage $board_repo,
        private Medoo $db,
        private array $exclude_tags
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
        /** @var array */
        $exclude_tags = $req->getParams('exclude_tags') ? $req->getParams('exclude_tags') : $this->exclude_tags;

        /** @var int */
        $limit = $req->getParams('limit') ? $req->getParams('limit') : 20;

        /** @var int */
        $offset = $req->getParams('offset') ? $req->getParams('offset') : 0;

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

        $results['boards'] = $this->board_repo->find($exclude_tags);

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
