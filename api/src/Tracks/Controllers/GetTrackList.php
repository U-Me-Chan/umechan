<?php

namespace PK\Tracks\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Tracks\TrackRepository;

final class GetTrackList
{
    public function __construct(
        private TrackRepository $track_repo
    ) {
    }

    public function __invoke(Request $req, array $vars = []): Response
    {
        list($tracks, $count) = $this->track_repo->findMany($req->getParams());

        return new Response(['tracks' => $tracks, $count]);
    }
}
