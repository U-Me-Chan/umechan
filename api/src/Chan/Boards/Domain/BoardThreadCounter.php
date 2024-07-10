<?php

namespace PK\Chan\Boards\Domain;

use PK\Shared\Domain\IntValue;

class BoardThreadCounter extends IntValue
{
    public function bump(): void
    {
        $this->value++;
    }
}
