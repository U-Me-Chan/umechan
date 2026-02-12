<?php

namespace PK\Posts\Events\FilestoreEvents;

use PK\Events\FilestoreEventPayload;

final class DeleteFilePayload extends FilestoreEventPayload
{
    public function __construct(
        public readonly string $filename
    ) {
    }

    public function toArray(): array
    {
	return get_object_vars($this);
    }
}
