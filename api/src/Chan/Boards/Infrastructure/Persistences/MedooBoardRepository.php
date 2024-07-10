<?php

namespace PK\Chan\Boards\Infrastructure\Persistences;

use PK\Chan\Boards\Domain\Board;
use PK\Chan\Boards\Infrastructure\IBoardRepository;

final class MedooBoardRepository implements IBoardRepository
{
    public function findMany(array $filters = [], array $ordering = []): array
    {
        return [];
    }

    public function findOne(array $filters = []): Board
    {
    }

    public function save(Board $board): int
    {
        return 0;
    }
}
