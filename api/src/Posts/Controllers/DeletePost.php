<?php

namespace PK\Posts\Controllers;

use Evenement\EventEmitter;
use PK\Events\Event\EventType;
use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\IPostRepository;
use PK\Posts\Post\Post;

final class DeletePost
{
    public function __construct(
        private IPostRepository $post_storage,
        private EventEmitter $events
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        if (!$req->getParams('password')) {
            return (new Response([], 400))->setException(new \InvalidArgumentException('Укажите пароль для удаления поста'));
        }

        try {
            /** @var Post */
            $post = $this->post_storage->findOne(['id' => $vars['id']]);
        } catch (\OutOfBoundsException) {
            return new Response([], 404);
        }

        if (!hash_equals($req->getParams('password'), $post->password)) {
            return new Response([], 403);
        }

        $post->erase();

        $this->post_storage->save($post);

        $this->events->emit(EventType::PostDeleted->value, ['post_id' => $post->id]);

        return new Response([], 204);
    }
}
