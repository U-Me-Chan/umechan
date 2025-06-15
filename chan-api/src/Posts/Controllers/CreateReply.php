<?php

namespace PK\Posts\Controllers;

use InvalidArgumentException;
use OutOfBoundsException;
use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\Services\PostFacade;

final class CreateReply
{
    public function __construct(
        private PostFacade $post_facade
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        $thread_id = $vars['id'];

        if (!$req->getParams('message')) {
            return (new Response([], 400))
                ->setException(new InvalidArgumentException('Необходимо передать message'));
        }

        $params = [];


        if ($req->getParams('poster')) {
            $params['poster'] = $req->getParams('poster');
        }

        if ($req->getParams('subject')) {
            $params['subject'] = $req->getParams('subject');
        }

        if ($req->getParams('sage')) {
            $params['sage'] = $req->getParams('sage');
        }

        try {
            $data = $this->post_facade->createReplyOnThread(
                $thread_id,
                $req->getParams('message'),
                $params
            );
        } catch (OutOfBoundsException) {
            return new Response([], 404);
        }

        return new Response($data, 201);
    }
}
