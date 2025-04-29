<?php

namespace PK\Posts\Controllers;

use Exception;
use InvalidArgumentException;
use OutOfBoundsException;
use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\PostStorage;
use PK\Posts\Post;
use PK\Boards\Board\Board;
use PK\Boards\BoardStorage;
use PK\Events\Services\EventTrigger;

final class CreateThread
{
    public function __construct(
        private BoardStorage $board_storage,
        private PostStorage $post_storage,
        private EventTrigger $event_trigger
    ) {
    }

    public function __invoke(Request $req): Response
    {
        if (!$req->getParams('tag')) {
            return (new Response([], 400))->setException(new InvalidArgumentException("Не передан tag"));
        }

        if (!$req->getParams('message')) {
            return (new Response([], 400))->setException(new InvalidArgumentException("Не передан message"));
        }

        try {
            /** @var Board */
            $board = $this->board_storage->findByTag($req->getParams('tag'));
        } catch (OutOfBoundsException) {
            return (new Response([], 404))->setException(new Exception('Нет доски с таким тегом'));
        }

        /** @var Post */
        $post = Post::draft($board, null, $req->getParams('message'));

        if ($req->getParams('poster')) {
            $post->poster = $req->getParams('poster');
        }

        if ($req->getParams('subject')) {
            $post->subject = $req->getParams('subject');
        }

        $id = $this->post_storage->save($post);

        $this->event_trigger->triggerPostCreated($id);
        $this->event_trigger->triggerBoardUpdated($board->id);

        return new Response(['post_id' => $id, 'password' => $post->password], 201);
    }
}
