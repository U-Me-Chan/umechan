<?php

namespace PK\Controllers;

use PK\Events\EventType;
use PK\V1_Events\Event;
use PK\Database\EventRepository;
use PK\Database\PostRepository;
use PK\Http\Request;
use PK\Http\Response;
use PK\Exceptions\Post\PostNotFound;
use PK\Database\Post\Post;

class PostDeleter
{
    public function __construct(
        private PostRepository $post_repository,
        private EventRepository $event_repository
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        if (!$req->getParams('password')) {
            return (new Response([], 400))->setException(new \InvalidArgumentException('Укажите пароль для удаления поста'));
        }

        try {
            /** @var Post */
            $post = $this->post_repository->findById($vars['id']);
        } catch (PostNotFound $e) {
            return (new Response([], 404))->setException($e);
        }

        if (hash_equals($req->getParams('password'), $post->getPassword())) {
            $post->setSubject('⬛⬛⬛⬛⬛⬛⬛⬛⬛');
            $post->setPoster('⬛⬛⬛⬛⬛⬛⬛⬛⬛');
            $message = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';
            $message = <<<EOT
{$message}

Данные удалены пользователем
EOT;
            $post->setMessage($message);

            $this->post_repository->update($post);

            $this->event_repository->save(Event::fromState([
                "event_type" => EventType::PostDeleted,
                "timestamp" => time(),
                "post_id" => $post->getId(),
                "board_id" => null,
            ]));

            return new Response([], 204);
        }

        return new Response([], 401);
    }
}
