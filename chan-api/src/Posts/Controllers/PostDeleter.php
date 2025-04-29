<?php

namespace PK\Posts\Controllers;

use Exception;
use OutOfBoundsException;
use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\Post;
use PK\Events\Event\Event;
use PK\Events\EventStorage;
use PK\Events\Event\EventType;
use PK\Posts\PostStorage;

class PostDeleter
{
    public function __construct(
        private PostStorage $post_storage,
        private EventStorage $event_storage
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        if (!$req->getParams('password')) {
            return (new Response([], 400))
                ->setException(new \InvalidArgumentException('Укажите пароль для удаления поста'));
        }

        try {
            /** @var Post */
            $post = $this->post_storage->findById($vars['id']);
        } catch (OutOfBoundsException $e) {
            return (new Response([], 404))->setException($e);
        }

        if (hash_equals($req->getParams('password'), $post->password)) {
            $post->subject = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';
            $post->poster = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';
            $message = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';
            $message = <<<EOT
{$message}

Данные удалены пользователем
EOT;
            $post->message = $message;

            $this->post_storage->save($post);

            $this->event_storage->save(Event::fromArray([
                'id' => 0,
                'event_type' => EventType::PostDeleted->name,
                'timestamp'  => time(),
                'post_id'    => $post->id,
                'board_id'   => null
            ]));

            return new Response([], 204);
        }

        return (new Response([], 401))->setException(new Exception('Неверный пароль поста'));
    }
}
