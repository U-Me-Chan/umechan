<?php

namespace PK\Boards\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Boards\IBoardRepository;

final class GetBoardList
{
    public function __construct(
        private IBoardRepository $storage
    ) {
    }

    public function __invoke(Request $req): Response
    {
        $exclude_tags = $req->getParams('exclude_tags') ? $req->getParams('exclude_tags') : ['fap', 'test'];

        foreach ($exclude_tags as $k => $tag) {
            if (empty($tag)) {
                unset($exclude_tags[$k]);
            }
        }

        list($boards, $count) = $this->storage->findMany(['exclude_tags' => $exclude_tags]);

        return new Response(['boards' => $boards, 'count' => $count], 200);
    }
}
