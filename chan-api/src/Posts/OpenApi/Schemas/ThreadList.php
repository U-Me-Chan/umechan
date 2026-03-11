<?php

namespace PK\Posts\OpenApi\Schemas;

use OpenApi\Attributes as OA;
use PK\Boards\Board;
use PK\Posts\Post;

#[OA\Schema]
abstract class ThreadList
{
    #[OA\Property(description: 'Общее количество нитей для условий запроса')]
    public int $count;

    /**
     * @var Board[] $boards
     */
    #[OA\Property(description: 'Список досок', items: new OA\Items(ref: Board::class))]
    public array $boards;

    /**
     * @var Post[] $posts
     */
    #[OA\Property(description: 'Список нитей с ответами на них', items: new OA\Items(ref: Post::class))]
    public array $posts;
}
