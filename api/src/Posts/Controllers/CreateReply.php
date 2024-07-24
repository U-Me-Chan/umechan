<?php

namespace PK\Posts\Controllers;

use Evenement\EventEmitter;
use PK\Events\Event\EventType;
use PK\Http\Request;
use PK\Http\Response;
use PK\Passports\IPassportRepository;
use PK\Passports\Passport\Password;
use PK\Posts\IPostRepository;
use PK\Posts\Post\IsVerifyPoster;
use PK\Posts\Post\Post;
use PK\Posts\Post\Poster;

final class CreateReply
{
    public function __construct(
        private IPostRepository $post_storage,
        private IPassportRepository $passport_repo,
        private EventEmitter $events
    ) {
    }

    public function __invoke(Request $req, array $vars): Response
    {
        if ($req->getParams('message') == null) {
            return (new Response([], 400))->setException(new \InvalidArgumentException('Не передано сообщение'));
        }

        try {
            $thread = $this->post_storage->findOne(['id' => $vars['id']]);
        } catch (\OutOfBoundsException) {
            return (new Response([], 404))->setException(new \OutOfBoundsException('Нет такой нити'));
        }

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

        $post = Post::draft(
            $thread->board,
            $thread->id,
            $req->getParams('message'),
            $poster,
            $req->getParams('subject') ? $req->getParams('subject') : ''
        );

        $id = $this->post_storage->save($post);

        $this->events->emit(EventType::PostCreated->value, ['post_id' => $id]);

        if ($thread->replies_count <= 500 && !$req->getParams('sage')) {
            $this->events->emit(EventType::ThreadUpdateTriggered->value, ['thread_id' => $thread->id]);
        }

        return new Response(['post_id' => $id, 'password' => $post->password], 201);
    }
}
