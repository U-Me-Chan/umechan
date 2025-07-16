<?php

namespace PK\Boards\OpenApi\Schemas;

use OpenApi\Attributes as OA;
use PK\Boards\Board\Board;

#[OA\Schema]
abstract class BoardList
{
    #[OA\Property(
        description: 'Список досок',
        type: 'array',
        items: new OA\Items(ref: Board::class)
    )]
    public array $boards;
}
