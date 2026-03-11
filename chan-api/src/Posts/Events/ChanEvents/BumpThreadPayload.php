<?php

namespace PK\Posts\Events\ChanEvents;

use PK\Events\ChanEventPayload;
use PK\Posts\Post;

final class BumpThreadPayload extends ChanEventPayload
{
    public function __construct(
        public readonly Post $thread
    ) {
    }

    /**
     * @return array{
     *     id: int,
     *     poster: string,
     *     subject: string,
     *     message: string,
     *     truncated_message: string,
     *     timestamp: int,
     *     board: array{
     *         id: int,
     *         tag: string,
     *         name: string,
     *         new_posts_count: int,
     *         threads_count: int,
     *         is_public: bool
     *     },
     *     password: string,
     *     replies_count: int,
     *     is_verify: bool,
     *     is_sticky: bool,
     *     is_blocked: bool,
     *     media: array{
     *         "images": list<array{link: string, preview: string, type: 'image'}>,
     *         "videos": list<array{link: string, preview: string, type: 'video'}>,
     *         "youtubes": list<array{link: string, preview: string}>
     *     }
     * }
     */
    public function toArray(): array
    {
        $data = $this->thread->jsonSerialize();

        unset(
            $data['parent_id'],
            $data['updated_at'],
            $data['replies']
        );

        return $data;
    }
}
