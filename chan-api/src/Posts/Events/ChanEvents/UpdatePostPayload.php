<?php

namespace PK\Posts\Events\ChanEvents;

use PK\Events\ChanEventPayload;

final class UpdatePostPayload extends ChanEventPayload
{
    /**
     * @param array{
     *     poster?: string,
     *     subject?: string,
     *     message?: string,
     *     is_sticky?: bool
     * } $changed_fields
     */
    public function __construct(
        public readonly array $changed_fields = []
    ) {
    }


    /**
     * @return array{
     *     poster?: string,
     *     subject?: string,
     *     message?: string,
     *     is_sticky?: bool
     * }
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
