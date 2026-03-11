<?php

namespace PK\Feed\Controllers;

use Medoo\Medoo;

use OpenApi\Attributes as OA;
use PK\Boards\Board\PublicFlag;
use PK\OpenApi\Schemas\Response;

use PK\Http\Request;
use PK\Http\Responses\JsonResponse;

use PK\Boards\Services\BoardService;

use PK\Feed\OpenApi\Schemas\Feed;

#[OA\Get(
    path: '/api/board/all',
    operationId: 'getFeed',
    summary: 'Получить список последних постов на чане',
    tags: ['post'],
    parameters: [
        new OA\Parameter(
            name: 'limit',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64',
                default: 20
            )
        ),
        new OA\Parameter(
            name: 'offset',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64',
                default: 0
            )
        ),
        new OA\Parameter(
            name: 'exclude_tags[]',
            in: 'query',
            description: 'Исключаемые теги досок',
            required: false,
            schema: new OA\Schema(
                type: 'array',
                items: new OA\Items(type: 'string')

            )
        ),
        new OA\Parameter(
            name: 'query',
            in: 'query',
            description: 'Строка для поиска по телу или заголовку поста',
            required: false,
            schema: new OA\Schema(
                type: 'string'
            )
        )
    ],
    deprecated: true
)]
#[Response(
    response: 200,
    description: 'Cписок последних постов на чане и список досок',
    payload_reference: Feed::class
)]
class BoardsFetcher
{
    public function __construct(
        private BoardService $board_service,
        private Medoo $db
    ) {
    }

    public function __invoke(Request $req): JsonResponse
    {
        /** @var string[] */
        $exclude_tags = $req->getParams('exclude_tags', []);

        /** @var int */
        $limit = $req->getParams('limit') ? $req->getParams('limit') : 20;

        /** @var int */
        $offset = $req->getParams('offset') ? $req->getParams('offset') : 0;

        $conditions = [
            'ORDER' => [
                'posts.timestamp' => 'DESC'
            ],
            'AND' => [
                'boards.is_public' => PublicFlag::yes->name
            ]
        ];

        if (!empty($exclude_tags)) {
            $conditions['AND']['boards.tag[!]'] = $exclude_tags;
            unset($conditions['AND']['boards.is_public']);
        }

        $limiting = [
            'LIMIT' => [$offset, $limit],
        ];

        if ($req->getParams('query')) {
            $conditions['AND']['OR'] = [
                'posts.subject[~]' => "%{$req->getParams('query')}%",
                'posts.message[~]' => "%{$req->getParams('query')}%"
            ];
        }

        $results['boards'] = $this->board_service->getBoardList($exclude_tags);
        $results['count']  = $this->db->count(
            'posts', [
                '[>]boards' => [
                    'board_id' => 'id'
                ]
            ],
            '*',
            $conditions
        );

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
              array_merge($conditions, $limiting)
          )
        );

        return new JsonResponse($results, 200);
    }
}
