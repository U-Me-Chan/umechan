<?php

namespace PK\Posts\Controllers;

use PK\Events\Event\Event;
use PK\Events\EventStorage;
use PK\Events\Event\EventType;
use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\PostStorage;
use PK\Posts\Post\Post;

final class CreateReply
{
    public function __construct(
        private PostStorage $post_storage,
        private EventStorage $event_storage,
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        $parent_id = $vars['id'];

        if ($req->getParams('message') == null) {
            return new Response([], 400);
        }

        try {
            $thread = $this->post_storage->findById($parent_id);
        } catch (\OutOfBoundsException $e) {
            return new Response([], 404);
        }

        $post = Post::draft($thread->board, $parent_id, $req->getParams('message'));

        if ($req->getParams('poster')) {
            $post->poster = $req->getParams('poster');
        }

        if ($req->getParams('subject')) {
            $post->subject = $req->getParams('subject');
        }

        $id = $this->post_storage->save($post);

        if ($thread->replies_count < 500 && !$req->getParams('sage')) {
            $thread->updated_at = time();

            $this->post_storage->save($thread);

            $this->event_storage->save(Event::fromArray([
                "id" => 0,
                "event_type" => EventType::ThreadUpdateTriggered->name,
                "timestamp" => time(),
                "post_id" => $parent_id,
                "board_id" => null,
            ]));
        }

        $this->event_storage->save(Event::fromArray([
            "id" => 0,
            "event_type" => EventType::PostCreated->name,
            "timestamp" => time(),
            "post_id" => $post->id,
            "board_id" => null,
        ]));

        return new Response(['post_id' => $id, 'password' => $post->password], 201);
    }
}
