<?php

namespace PK\Boards\Events\ChanEvents;

use PK\Boards\Board;
use PK\Events\ChanEventPayload;

final class CreateBoardPayload extends ChanEventPayload
{
    public function __construct(
        public Board $board
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
