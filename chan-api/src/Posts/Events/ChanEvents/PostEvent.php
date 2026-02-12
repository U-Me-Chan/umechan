<?php

namespace PK\Posts\Events\ChanEvents;

use PK\Events\ChanEvent;

abstract class PostEvent extends ChanEvent
{
    public string $topic = 'chan.posts';
}
