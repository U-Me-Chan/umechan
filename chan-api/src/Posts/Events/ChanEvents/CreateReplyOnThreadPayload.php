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
        $data = $this->post->jsonSerialize();

        unset(
            $data['updated_at'],
            $data['replies_count']
        );

        return $data;
    }
}
