<?php

namespace PK\Boards\Events\ChanEvents;

use PK\Events\ChanEvent;

final class UpdateBoard extends ChanEvent
{
    public string $topic = 'chan.boards';
}
