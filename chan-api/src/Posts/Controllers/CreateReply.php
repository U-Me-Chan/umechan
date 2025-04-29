<?php

namespace PK\Posts\Controllers;

use InvalidArgumentException;
use PK\Events\Event\Event;
use PK\Events\Services\EventTrigger;
use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\PostStorage;
use PK\Posts\Post;

final class CreateReply
{
    public function __construct(
        private PostStorage $post_storage,
        private EventTrigger $event_trigger
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        $parent_id = $vars['id'];

        if (!$req->getParams('message')) {
            return (new Response([], 400))->setException(new InvalidArgumentException('Необходимо передать message'));
        }

        try {
            $thread = $this->post_storage->findById($parent_id);
        } catch (\OutOfBoundsException) {
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

            $this->event_trigger->triggerThreadUpdated($parent_id);
        }

        $this->event_trigger->triggerPostCreated($post->id);

        return new Response(['post_id' => $id, 'password' => $post->password], 201);
    }
}
