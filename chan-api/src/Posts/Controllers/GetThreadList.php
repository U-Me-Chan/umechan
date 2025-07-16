<?php

namespace PK\Posts\Controllers;

use OpenApi\Attributes as OA;
use OutOfBoundsException;
use PK\Boards\BoardStorage;
use PK\Posts\OpenApi\Schemas\ThreadList;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\OpenApi\Schemas\Error;
use PK\OpenApi\Schemas\Response;
use PK\Posts\PostStorage;

#[OA\Get(
    path: '/api/v2/board/{tags}',
    operationId: 'getThreadList',
    summary: 'Получить список тредов для доски/досок',
    tags: ['post'],
    parameters: [
        new OA\Parameter(
            name: 'tags',
            in: 'path',
            required: true,
            description: "Тег или набор тегов доски, разделённых символом '+'",
            schema: new OA\Schema(
                type: 'string'
            )
        ),
        new OA\Parameter(
            name: 'limit',
            in: 'query',
            description: 'Количество тредов в ответе',
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64'
            )
        ),
        new OA\Parameter(
            name: 'offset',
            in: 'query',
            description: 'Смещение в списке относительно первого треда',
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64'
            )
        )
    ]
)]
#[Response(
    200,
    'Список нитей с ответами, досок и количество нитей на условие запроса',
    payload_reference: ThreadList::class
)]
#[Error(
    400,
    'Доска не существует',
    OutOfBoundsException::class
)]
final class GetThreadList
{
    public function __construct(
        private PostStorage $post_storage,
        private BoardStorage $board_storage
    ) {
    }

    public function __invoke(Request $req, array $vars): JsonResponse
    {
        $limit  = $req->getParams('limit') ? $req->getParams('limit') : 20;
        $offset = $req->getParams('offset') ? $req->getParams('offset') : 0;

        $tags = explode('+', $vars['tags']);

        try {
            list($posts, $count) = $this->post_storage->find($limit, $offset, $tags);
        } catch (\OutOfBoundsException) {
            return new JsonResponse([], 400);
        }

        return new JsonResponse(['count' => $count, 'posts' => $posts, 'boards' => $this->board_storage->find()]);
    }
}
