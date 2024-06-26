<?php

namespace PK\Boards\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Boards\BoardStorage;

final class GetBoardList
{
    public function __construct(
        private BoardStorage $storage
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

        $boards = $this->storage->find($exclude_tags);

        return new Response(['boards' => $boards], 200);
    }
}
