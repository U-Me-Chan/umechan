<?php

namespace PK\Posts\Controllers;

use Exception;
use InvalidArgumentException;
use OutOfBoundsException;
use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\Post;
use PK\Events\Services\EventTrigger;
use PK\Posts\PostStorage;

class PostDeleter
{
    public function __construct(
        private PostStorage $post_storage,
        private EventTrigger $event_trigger
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        if (!$req->getParams('password')) {
            return (new Response([], 400))
                ->setException(new InvalidArgumentException('Укажите пароль для удаления поста'));
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
            $post->is_verify = false;

            $this->post_storage->save($post);

            $this->event_trigger->triggerPostDeleted($post->id);

            return new Response([], 204);
        }

        return (new Response([], 401))->setException(new Exception('Неверный пароль поста'));
    }
}
