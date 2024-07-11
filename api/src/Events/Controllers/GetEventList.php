<?php

namespace PK\Events\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Events\IEventRepository;

final class GetEventList
{
    public function __construct(
        private IEventRepository $storage
    ) {
    }

    public function __invoke(Request $req): Response
    {
        $limit = $req->getParams('limit') ? $req->getParams('limit') : 20;
        $offset = $req->getParams('offset') ? $req->getParams('offset') : 0;
        $from_timestamp = $req->getParams('from_timestamp') ? $req->getParams('from_timestamp') : 0;

        try {
            list($posts, $count) = $this->storage->findMany(['limit' => $limit, 'offset' => $offset, 'timestamp_from' => $from_timestamp]);
        } catch (\OutOfBoundsException) {
            return new Response([], 400);
        }

        return new Response(['count' => $count, 'events' => $posts]);
    }
}
