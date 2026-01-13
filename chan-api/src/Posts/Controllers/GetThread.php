<?php

namespace PK\Posts\Controllers;

use OutOfBoundsException;
use OpenApi\Attributes as OA;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\OpenApi\Schemas\Error;
use PK\OpenApi\Schemas\Response;
use PK\Posts\Exceptions\ThreadNotFoundException;
use PK\Posts\OpenApi\Schemas\ThreadData;
use PK\Posts\Services\PostFacade;

#[OA\Get(
    path: '/api/v2/post/{id}',
    operationId: 'getThread',
    summary: 'Получить тред и его ответы',
    tags: ['post'],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            description: 'Идентификатор треда',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64'
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
            name: 'no_board_list',
            in: 'query',
            description: 'Не возвращать список досок в ответе',
            required: false,
            schema: new OA\Schema(
                type: 'string'
            )
        )
    ]
)]
#[Response(
    response: 200,
    description: 'Данные поста и список досок',
    payload_reference: ThreadData::class
)]
#[Error(
    404,
    'Тред не найден',
    ThreadNotFoundException::class
)]
final class GetThread
{
    public function __construct(
        private PostFacade $post_facade,
        private array $exclude_tags
    ) {
    }

    public function __invoke(Request $req, array $vars): JsonResponse
    {
        /** @var int */
        $id = $vars['id'];

        $exclude_tags  = $req->getParams('exclude_tags', $this->exclude_tags);
        $no_board_list = $req->getParams('no_board_list') ? true : false;

        try {
            list($thread, $boards) = $this->post_facade->getThread($id, $exclude_tags, $no_board_list);

            return new JsonResponse(['thread_data' => $thread, 'boards' => $boards]);
        } catch (ThreadNotFoundException $e) {
            return new JsonResponse([], 404)->setException($e);
        }
    }
}
