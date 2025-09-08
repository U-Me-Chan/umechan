<?php

namespace PK\Events\Services;

use PK\Events\Event;
use PK\Events\Event\EventType;
use PK\Events\EventStorage;

class EventTrigger
{
    public function __construct(
        private EventStorage $event_storage
    ) {
    }

    public function triggerThreadUpdated(int $thread_id): void
    {
        $this->event_storage->save(Event::draft(EventType::ThreadUpdateTriggered, $thread_id));
    }

    public function triggerPostCreated(int $post_id): void
    {
        $this->event_storage->save(Event::draft(EventType::PostCreated, $post_id));
    }

    public function triggerBoardUpdated(int $board_id): void
    {
        $this->event_storage->save(Event::draft(type: EventType::BoardUpdateTriggered, board_id: $board_id));
    }

    public function triggerPostDeleted(int $post_id): void
    {
        $this->event_storage->save(Event::draft(EventType::PostDeleted, $post_id));
    }
}
