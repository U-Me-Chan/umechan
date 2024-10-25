<?php

namespace PK\Posts\Controllers;

use Evenement\EventEmitter;
use PK\Http\Request;
use PK\Http\Response;
use PK\Posts\Post\Post;
use PK\Boards\Board\Board;
use PK\Boards\IBoardRepository;
use PK\Events\Event\EventType;
use PK\Passports\IPassportRepository;
use PK\Passports\Passport\Passport;
use PK\Passports\Passport\Password;
use PK\Posts\IPostRepository;
use PK\Posts\Post\IsVerifyPoster;
use PK\Posts\Post\Poster;

final class CreateThread
{
    public function __construct(
        private IBoardRepository $board_storage,
        private IPostRepository $post_storage,
        private IPassportRepository $passport_repo,
        private EventEmitter $events
    ) {
    }

    public function __invoke(Request $req): Response
    {
        if ($req->getParams('tag') == null) {
            return (new Response([], 400))->setException(new \InvalidArgumentException('Не задан тег доски'));
        }

        if ($req->getParams('message') == null) {
            return (new Response([], 400))->setException(new \InvalidArgumentException('Не задано сообщение'));
        }

        if (empty($req->getParams('message'))) {
            return (new Response([], 400))->setException(new \InvalidArgumentException('Сообщение не может быть пустым'));
        }

        /** @var Board */
        $board = $this->board_storage->findOne(['tag' => $req->getParams('tag')]);

        if ($req->getParams('poster')) {
            try {
                /** @var Passport */
                $passport = $this->passport_repo->findOne(['hash' => Password::draft($req->getParams('poster'))->toString()]);

                $poster = Poster::draft($passport->name->toString(), IsVerifyPoster::YES);
            } catch (\OutOfBoundsException) {
                $poster = Poster::draft($req->getParams('poster'), IsVerifyPoster::NO);
            }
        } else {
            $poster = Poster::draft('Anonymous', IsVerifyPoster::NO);
        }

        /** @var Post */
        $post = Post::draft(
            $board,
            null,
            $req->getParams('message'),
            $poster,
            $req->getParams('subject') ? $req->getParams('subject') : ''
        );

        $id = $this->post_storage->save($post);

        $this->events->emit(EventType::PostCreated->value, ['post_id' => $id]);
        $this->events->emit(EventType::BoardUpdateTriggered->value, ['board_id' => $board->id]);

        return new Response(['post_id' => $id, 'password' => $post->password], 201);
    }
}
