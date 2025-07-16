<?php

namespace PK\Posts\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema]
class PostCreated
{
    #[OA\Property]
    public int $post_id;

    #[OA\Property]
    public string $password;
}
