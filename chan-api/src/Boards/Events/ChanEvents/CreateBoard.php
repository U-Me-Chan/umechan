<?php

namespace PK\Boards\Events\ChanEvents;

use PK\Events\ChanEvent;

final class CreateBoard extends ChanEvent
{
    public string $topic = 'chan.boards';
}
