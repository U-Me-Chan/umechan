<?php

namespace PK\Posts\Controllers;

use PK\Events\Event\Event;
use PK\Events\EventStorage;
use PK\Events\Event\EventType;
use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\PostStorage;
use PK\Posts\Post;

final class DeletePost
{
    public function __construct(
        private PostStorage $post_storage,
        private EventStorage $event_storage,
        private string $key
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        if ($req->getHeaders('HTTP_KEY') == null) {
            return (new Response([], 401))->setException(new \InvalidArgumentException("Не задан мастер-ключ"));
        }

        if ($req->getHeaders('HTTP_KEY') !== $this->key) {
            return (new Response([], 401))->setException(new \InvalidArgumentException("Неверный мастер-ключ"));
        }

        $reason = $req->getHeaders('HTTP_REASON') ? $req->getHeaders('HTTP_REASON') : 'Не указано';

        $id = $vars['id'];

        try {
            /** @var Post */
            $post = $this->post_storage->findById($id);

            $post->subject = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';
            $post->poster = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';
            $post->message = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';

            $post->message = <<<EOT
{$post->message}

Данные удалены по причине: {$reason}
EOT;

            $this->post_storage->save($post);
            $this->event_storage->save(Event::fromArray([
                "id" => 0,
                "event_type" => EventType::PostDeleted->name,
                "timestamp" => time(),
                "post_id" => $id,
                "board_id" => null,
            ]));

            return new Response([], 204);
        } catch (\OutOfBoundsException $e) {
            return (new Response([], 404))->setException(new \OutOfBoundsException("Нет такого поста"));
        }
    }
}
