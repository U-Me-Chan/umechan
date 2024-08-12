<?php

namespace PK\Boards\Controllers;

use OpenApi\Attributes as OA;

use PK\Http\Request;
use PK\Http\Response;
use PK\Boards\IBoardRepository;
use PK\Boards\ResponseSchemas\BoardListResponseSchema;

#[OA\Get(path: '/api/v2/board')]
#[OA\Response(
    response: 200,
    description: 'Return board list',
    content: new OA\JsonContent(ref: '#/components/schemas/boardList')
)]
final class GetBoardList
{
    public function __construct(
        private IBoardRepository $storage
    ) {
    }

    public function __invoke(Request $req): Response
    {
        $exclude_tags = $req->getParams('exclude_tags') ? $req->getParams('exclude_tags') : ['fap', 'und'];

        foreach ($exclude_tags as $k => $tag) {
            if (empty($tag)) {
                unset($exclude_tags[$k]);
            }
        }

        list($boards, $count) = $this->storage->findMany(['exclude_tags' => $exclude_tags]);

        return new Response(new BoardListResponseSchema($boards, $count), 200);
    }
}
