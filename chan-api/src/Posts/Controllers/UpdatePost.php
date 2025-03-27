<?php

namespace PK\Posts\Controllers;

use PK\Http\Request;
use PK\Http\Response;

final class UpdatePost
{
    public function __construct(
        private string $key
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        if ($req->getHeaders('HTTP_KEY') == null) {
            return new Response([], 401);
        }

        if ($req->getHeaders('HTTP_KEY') !== $this->key) {
            return new Response([], 401);
        }

        // todo: post update functionality
        // todo: dont forget about event creation

        return new Response();
    }
}
