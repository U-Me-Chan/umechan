<?php

namespace PK\Events\EventCallbacks;

use PK\Events\AbstractEventCallback;
use PK\Events\Event\Event;
use PK\Events\Event\EventType;
use PK\Events\IEventRepository;
use PK\Posts\IPostRepository;

class ThreadUpdatedHandler extends AbstractEventCallback
{
    public function __construct(
        protected IEventRepository $event_repo,
        protected IPostRepository $post_repo
    ) {
    }


    public function __invoke(int $thread_id): void
    {
        try {
            $thread = $this->post_repo->findOne(['id' => $thread_id]);
        } catch (\OutOfBoundsException) {
            throw new \RuntimeException();
        }

        $thread->updated_at = time();

        $this->post_repo->save($thread);

        $event = Event::draft(EventType::ThreadUpdateTriggered, $thread_id);

        $this->event_repo->save($event);
    }
}
