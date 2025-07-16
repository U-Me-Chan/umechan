<?php

namespace PK\Feed\OpenApi\Schemas;

use OpenApi\Attributes as OA;
use PK\Boards\Board\Board;
use PK\Feed\OpenApi\Schemas\RecentPost;

#[OA\Schema]
abstract class Feed
{
    #[OA\Property(
        description: 'Список досок',
        items: new OA\Items(ref: Board::class)
    )]
    public array $boards;

    #[OA\Property(
        description: 'Список постов',
        items: new OA\Items(ref: RecentPost::class)
    )]
    public array $posts;
}
