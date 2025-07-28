<?php

namespace PK\Posts\Controllers;

use OpenApi\Attributes as OA;
use OutOfBoundsException;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\OpenApi\Schemas\Error;
use PK\OpenApi\Schemas\Response;
use PK\Boards\BoardStorage;
use PK\Posts\OpenApi\Schemas\ThreadData;
use PK\Posts\PostStorage;
use PK\Posts\Post;

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
    OutOfBoundsException::class
)]
final class GetThread
{
    public function __construct(
        private PostStorage $storage,
        private BoardStorage $board_storage,
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
            /** @var Post */
            $post = $this->storage->findById($id);
        } catch (\OutOfBoundsException $e) {
            return (new JsonResponse([], 404))->setException($e);
        }

        if (!$no_board_list) {
            $boards = $this->board_storage->find($exclude_tags);
        } else {
            $boards = [];
        }

        return new JsonResponse(['thread_data' => $post, 'boards' => $boards]);
    }
}
