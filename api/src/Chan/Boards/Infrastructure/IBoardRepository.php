<?php

namespace PK\Chan\Boards\Infrastructure;

use PK\Chan\Boards\Domain\Board;
use PK\Shared\Infrastructrure\IRepository;

interface IBoardRepository extends IRepository
{
    public function save(Board $board): int;
}
