<?php

namespace PK\Boards;

use PK\Base\IRepository;
use PK\Boards\Board\Board;

interface IBoardRepository extends IRepository
{
    public function findOne(array $filters = []): Board;
    public function save(Board $board): int;
    public function delete(Board $board): bool;
}
