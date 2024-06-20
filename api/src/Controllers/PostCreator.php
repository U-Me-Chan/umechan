<?php

namespace PK\Controllers;

use PK\Events\EventType;
use PK\V1_Events\Event;
use PK\Database\EventRepository;
use PK\Database\PostRepository;
use PK\Database\BoardRepository;
use PK\Database\Post\Post;
use PK\Http\Request;
use PK\Http\Response;
use PK\Exceptions\Post\PostNotFound;
use PK\Exceptions\Board\BoardNotFound;

class PostCreator
{
    public function __construct(
        private PostRepository $post_repository,
        private BoardRepository $board_repository,
        private EventRepository $event_repository,
    ) {
    }

    public function __invoke(Request $req): Response
    {
        if ($req->getParams('parent_id')) {
            try {
                /** @var Post */
                $parent_post = $this->post_repository->findById($req->getParams('parent_id'));
            } catch (PostNotFound $e) {
                return (new Response([], 400))->setException(new PostNotFound('Попытка ответа на несуществующий пост'));
            }

            $message = $req->getParams('message') ? $req->getParams('message') : throw new \InvalidArgumentException("Нельзя создать пост без сообщения");

            $post = new Post(
                $this->post_repository->getNewId(),
                $req->getParams('poster') ? $req->getParams('poster') : 'Anonymous',
                $req->getParams('subject') ? $req->getParams('subject') : '',
                $req->getParams('message') ? $req->getParams('message') : '',
                time(),
                $parent_post->getBoardId(),
                $req->getParams('parent_id'),
                time()
            );

            $new_post_id = $this->post_repository->save($post);

            $this->event_repository->save(Event::fromState([
                "event_type" => EventType::PostCreated,
                "timestamp" => time(),
                "post_id" => $new_post_id,
                "board_id" => null,
            ]));

            $replies_count = $this->post_repository->getRepliesCount($parent_post->getId());

            if (!$req->getParams('sage') && $replies_count < 500) {
                $parent_post->setUpdatedAt(time());
                $this->post_repository->update($parent_post); // bump thread

                $parent_post->setEstimate($parent_post->getEstimate() + 1);

                $this->event_repository->save(Event::fromState([
                    "event_type" => EventType::ThreadUpdateTriggered,
                    "timestamp" => time(),
                    "post_id" => $parent_post->getId(),
                    "board_id" => null,
                ]));
            }

            return new Response(['post_id' => $new_post_id, 'password' => $post->getPassword()], 201);
        }

        if (!$req->getParams('tag')) {
            return (new Response([], 400))->setException(new \InvalidArgumentException('Не задано тег доски, на которой должен быть создан пост'));
        }

        $board = $this->board_repository->findByTag($req->getParams('tag'));

        if (!$board) {
            return (new Response([], 400))->setException(new BoardNotFound('Не найдена доска с таким тегом'));
        }

        $post = new Post(
            $this->post_repository->getNewId(),
            $req->getParams('poster') ? $req->getParams('poster') : 'Anonymous',
            $req->getParams('subject') ? $req->getParams('subject') : '',
            $req->getParams('message') ? $req->getParams('message') : '',
            time(),
            $board->getId(),
            null,
            time()
        );

        $new_post_id = $this->post_repository->save($post);

        $this->event_repository->save(Event::fromState([
            "event_type" => EventType::PostCreated,
            "timestamp" => time(),
            "post_id" => $new_post_id,
            "board_id" => null,
        ]));

        $this->event_repository->save(Event::fromState([
            "event_type" => EventType::BoardUpdateTriggered,
            "timestamp" => time(),
            "post_id" => null,
            "board_id" => $board->getId(),
        ]));

        return new Response(['post_id' => $new_post_id, 'password' => $post->getPassword()], 201);
    }
}
