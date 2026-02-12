<?php

namespace PK\Boards\Controllers;

use OpenApi\Attributes as OA;
use PK\Http\Request;
use PK\Http\Responses\JsonResponse;
use PK\OpenApi\Schemas\Response;
use PK\Boards\OpenApi\Schemas\BoardList;
use PK\Boards\Services\BoardService;

#[OA\Get(
    path: '/api/v2/board',
    operationId: 'getBoardList',
    summary: 'Получить список досок',
    tags: ['board'],
    parameters: [
        new OA\Parameter(
            name: 'exclude_tags[]',
            in: 'query',
            description: 'Исключаемые теги досок',
            required: false,
            schema: new OA\Schema(
                type: 'array',
                items: new OA\Items(type: 'string')
            )
        )
    ],
)]
#[Response(
    response: 200,
    description: 'Список досок',
    payload_reference: BoardList::class
)]
final class GetBoardList
{
    public function __construct(
        private BoardService $board_service,
        private array $exclude_tags
    ) {
    }

    public function __invoke(Request $req): JsonResponse
    {
        $exclude_tags = $req->getParams('exclude_tags', $this->exclude_tags);

        $boards = $this->board_service->getBoardList($exclude_tags);

        return new JsonResponse(['boards' => $boards], 200);
    }
}
