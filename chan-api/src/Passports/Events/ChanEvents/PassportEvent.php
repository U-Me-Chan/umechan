<?php

namespace PK\Passports\Events\ChanEvents;

use PK\Events\ChanEvent;

class PassportEvent extends ChanEvent
{
    public string $topic = 'chan.passports';
}
