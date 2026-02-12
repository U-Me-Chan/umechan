<?php

namespace PK\Posts\Events\ChanEvents;

use PK\Events\ChanEventPayload;
use PK\Posts\Post;

final class CreateThreadPayload extends ChanEventPayload
{
    public function __construct(
        public readonly Post $thread
    ) {
    }

    public function toArray(): array
    {
        $data = $this->thread->jsonSerialize();

        unset(
            $data['bump_limit_reached'],
            $data['board_id'],
            $data['datetime'],
            $data['board'],
            $data['parent_id'],
            $data['updated_at'],
            $data['replies']
        );

        return $data;
    }
}
