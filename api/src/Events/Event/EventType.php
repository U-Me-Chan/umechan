<?php

namespace PK\Events\Event;

enum EventType: string
{
    case PostCreated           = 'PostCreated';
    case PostDeleted           = 'PostDeleted';
    case BoardUpdateTriggered  = 'BoardUpdateTriggered';
    case ThreadUpdateTriggered = 'ThreadUpdateTriggered';
}
