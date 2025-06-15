<?php

namespace PK\Posts\Controllers;

use InvalidArgumentException;
use OutOfBoundsException;
use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\Services\PostFacade;
use RuntimeException;

class PostDeleter
{
    public function __construct(
        private PostFacade $post_facade
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        if (!$req->getParams('password')) {
            return new Response([], 400)
                ->setException(new InvalidArgumentException('Укажите пароль для удаления поста'));
        }

        try {
            $this->post_facade->deletePostByAuthor($vars['id'], $req->getParams('password'));
        } catch (OutOfBoundsException $e) {
            return new Response([], 404)->setException($e);
        } catch (RuntimeException $e) {
            return new Response([], 401)->setException($e);
        }

        return new Response([], 204);
    }
}
