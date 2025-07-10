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
        private BoardStorage $board_storage
    ) {
    }

    public function __invoke(Request $req, array $vars): JsonResponse
    {
        /** @var int */
        $id = $vars['id'];

        try {
            /** @var Post */
            $post = $this->storage->findById($id);
        } catch (\OutOfBoundsException $e) {
            return (new JsonResponse([], 404))->setException($e);
        }

        return new JsonResponse(['thread_data' => $post, 'boards' => $this->board_storage->find()]);
    }
}
