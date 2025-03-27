<?php

namespace PK\Events\Event;

enum EventType
{
    case PostCreated;
    case PostDeleted;
    case BoardUpdateTriggered;
    case ThreadUpdateTriggered;
}
