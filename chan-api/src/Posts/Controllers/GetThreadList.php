<?php

namespace PK\Posts\Controllers;

use OpenApi\Attributes as OA;
use OutOfBoundsException;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\OpenApi\Schemas\Error;
use PK\OpenApi\Schemas\Response;
use PK\Posts\OpenApi\Schemas\ThreadList;
use PK\Posts\Services\PostFacade;

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
                format: 'int64',
                maxLength: 100,
                default: 20
            )
        ),
        new OA\Parameter(
            name: 'offset',
            in: 'query',
            description: 'Смещение в списке относительно первого треда',
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
        private PostFacade $post_facade,
        private array $exclude_tags
    ) {
    }

    public function __invoke(Request $req, array $vars): JsonResponse
    {
        $limit         = $req->getParams('limit') ? $req->getParams('limit') : 20;
        $offset        = $req->getParams('offset') ? $req->getParams('offset') : 0;
        $no_board_list = $req->getParams('no_board_list') ? true : false;

        $tags = explode('+', $vars['tags']);

        $exclude_tags = $req->getParams('exclude_tags', $this->exclude_tags);

        list($threads, $count, $boards) = $this->post_facade->getThreadList($tags, $limit, $offset, $exclude_tags, $no_board_list);

        return new JsonResponse([
            'count'  => $count,
            'posts'  => $threads,
            'boards' => $boards
        ]);
    }
}
