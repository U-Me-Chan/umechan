<?php

namespace PK\Posts\Controllers;

use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\PostStorage;
use PK\Posts\Post\Post;
use PK\Boards\Board\Board;
use PK\Boards\BoardStorage;
use PK\Events\Event;
use PK\Events\EventStorage;
use PK\Events\EventType;

final class CreateThread
{
    public function __construct(
        private BoardStorage $board_storage,
        private PostStorage $post_storage,
        private EventStorage $event_storage,
    ) {
    }

    public function __invoke(Request $req): Response
    {
        if ($req->getParams('tag') == null) {
            return (new Response([], 400))->setException(new \InvalidArgumentException("tag not bind param"));
        }

        if ($req->getParams('message') == null) {
            return (new Response([], 400))->setException(new \InvalidArgumentException("message not bind param"));
        }

        if (empty($req->getParams('message'))) {
            return (new Response([], 400))->setException(new \InvalidArgumentException("message param cannot be empty"));
        }

        /** @var Board */
        $board = $this->board_storage->findByTag($req->getParams('tag'));

        /** @var Post */
        $post = Post::draft($board, null, $req->getParams('message'));

        if ($req->getParams('poster')) {
            $post->poster = $req->getParams('poster');
        }

        if ($req->getParams('subject')) {
            $post->subject = $req->getParams('subject');
        }

        $id = $this->post_storage->save($post);

        $this->event_storage->save(Event::fromArray([
            "id" => 0,
            "event_type" => EventType::PostCreated,
            "timestamp" => time(),
            "post_id" => $id,
            "board_id" => null,
        ]));

        $this->event_storage->save(Event::fromArray([
            "id" => 0,
            "event_type" => EventType::BoardUpdateTriggered,
            "timestamp" => time(),
            "post_id" => null,
            "board_id" => $board->id,
        ]));

        return new Response(['post_id' => $id, 'password' => $post->password], 201);
    }
}
