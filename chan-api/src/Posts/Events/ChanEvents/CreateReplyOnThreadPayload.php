<?php

namespace PK\Posts\Events\ChanEvents;

use PK\Events\ChanEventPayload;
use PK\Posts\Post;

final class CreateReplyOnThreadPayload extends ChanEventPayload
{
    public function __construct(
        private Post $post
    ) {
    }

    public function toArray(): array
    {
        $data = $this->post->jsonSerialize();

        unset(
            $data['bump_limit_reached'],
            $data['board_id'],
            $data['datetime'],
            $data['board'],
            $data['updated_at'],
            $data['replies'],
            $data['replies_count']
        );

        return $data;
    }
}
