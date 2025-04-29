<?php

namespace PK\Events\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Events\EventStorage;

final class GetEventList
{
    public function __construct(private EventStorage $storage) {}

    public function __invoke(Request $req): Response
    {
        /** @var int */
        $limit = $req->getParams('limit') ? $req->getParams('limit') : 20;

        /** @var int */
        $offset = $req->getParams('offset') ? $req->getParams('offset') : 0;

        /** @var int */
        $from_timestamp = $req->getParams('from_timestamp') ? $req->getParams('from_timestamp') : 0;

        try {
            list($events, $count) = $this->storage->find($limit, $offset, $from_timestamp);
        } catch (\OutOfBoundsException) {
            return new Response([], 400);
        }

        return new Response(['count' => $count, 'events' => $events]);
    }
}
