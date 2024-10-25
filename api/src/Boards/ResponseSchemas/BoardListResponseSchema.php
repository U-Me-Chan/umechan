<?php

namespace PK\Boards\ResponseSchemas;

use OpenApi\Attributes as OA;

use PK\Base\AResponseSchema;
use PK\Boards\Board\Board;

#[OA\Schema(schema: 'boardList')]
class BoardListResponseSchema extends AResponseSchema
{
    public function __construct(
        #[OA\Property(items: new OA\Items(ref: '#/components/schemas/Board'))]
        public array $boards,
        #[OA\Property(description: 'Количество досок')]
        public int $count
    ) {
        foreach ($boards as $board) {
            if (!$board instanceof Board) {
                throw new \InvalidArgumentException();
            }
        }
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
