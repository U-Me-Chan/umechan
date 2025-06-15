<?php

namespace PK\Posts\Controllers;

use Exception;
use InvalidArgumentException;
use OutOfBoundsException;
use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\Services\PostFacade;

final class CreateThread
{
    public function __construct(
        private PostFacade $post_facade
    ) {
    }

    public function __invoke(Request $req): Response
    {
        if (!$req->getParams('tag')) {
            return new Response([], 400)
                ->setException(new InvalidArgumentException("Не передан tag"));
        }

        if (!$req->getParams('message')) {
            return new Response([], 400)
                ->setException(new InvalidArgumentException("Не передан message"));
        }

        try {
            $data = $this->post_facade->createThread(
                $req->getParams('tag'),
                $req->getParams('message')
            );
        } catch (OutOfBoundsException) {
            return new Response([], 404)
                ->setException(new Exception('Нет доски с таким тегом'));
        }

        return new Response($data, 201);
    }
}
