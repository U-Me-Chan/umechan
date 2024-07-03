<?php

namespace PK\Controllers;

use PK\Database\EventRepository;
use PK\Http\Request;
use PK\Http\Response;
use PK\Exceptions\Event\EventNotFound;

class EventFetcher
{
    public function __construct(
        private EventRepository $repository
    ) {
    }

    public function __invoke(Request $req)
    {
        try {
            $events = $this->repository->findFrom(
                $req->getParams('from_timestamp') ? $req->getParams('from_timestamp') : 0,
                $req->getParams('offset') ? $req->getParams('offset') : 0,
                $req->getParams('limit') ? $req->getParams('limit') : 10
            );
        } catch (EventNotFound) {
            return (new Response([], 404))->setException(new EventNotFound());
        }

        $results["events"] = $events;

        return new Response($results, 200);
    }
}
