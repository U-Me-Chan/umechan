<?php

namespace PK\Events\EventCallbacks;

use PK\Events\AbstractEventCallback;
use PK\Events\Event\Event;
use PK\Events\Event\EventType;

class PostDeletedHandler extends AbstractEventCallback
{
    public function __invoke(int $post_id): void
    {
        $event = Event::draft(EventType::PostDeleted, $post_id);

        $this->event_repo->save($event);
    }
}
