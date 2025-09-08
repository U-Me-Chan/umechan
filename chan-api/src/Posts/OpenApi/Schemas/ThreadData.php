<?php

namespace PK\Posts\OpenApi\Schemas;

use OpenApi\Attributes as OA;
use PK\Boards\Board;
use PK\Posts\Post;

#[OA\Schema]
abstract class ThreadData
{
    #[OA\Property(
        description: 'Данные нити',
        ref: '#/components/schemas/Post'
    )]
    public Post $thread_data;

    #[OA\Property(
        description: 'Список досок',
        type: 'array',
        items: new OA\Items(ref: Board::class)
    )]
    public array $boards;
}
