<?php

namespace PK\Posts\ResponseSchemas;

use OpenApi\Attributes as OA;

use PK\Base\AResponseSchema;
use PK\Posts\Post\Post;

#[OA\Schema(schema: 'Thread', properties: [
    new OA\Property(property: 'thread_data', type: 'object', ref: '#/components/schemas/Post')
])]
class ThreadResponseSchema extends AResponseSchema
{
    public function __construct(
        private Post $thread_data
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
