<?php

namespace PK\Events\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Events\EventStorage;

final class GetEventList
{
    public function __construct(private EventStorage $storage) {}

    public function __invoke(Request $req, array $vars): Response
    {
        $limit = $req->getParams('limit') ? $req->getParams('limit') : 20;
        $offset = $req->getParams('offset') ? $req->getParams('offset') : 0;
        $from_timestamp = $vars['from_timestamp'] ? $vars['from_timestamp'] : 0;

        try {
            list($posts, $count) = $this->storage->find($limit, $offset, $from_timestamp);
        } catch (\OutOfBoundsException $e) {
            return new Response([], 400);
        }

        return new Response(['count' => $count, 'posts' => $posts]);
    }
}
