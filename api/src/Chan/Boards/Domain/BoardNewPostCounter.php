<?php

namespace PK\Chan\Boards\Domain;

use PK\Shared\Domain\IntValue;

class BoardNewPostCounter extends IntValue
{
    public function bump(): void
    {
        $this->value++;
    }
}
