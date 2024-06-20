<?php

namespace PK\Events;

enum EventType
{
    case PostCreated;
    case PostDeleted;
    case BoardUpdateTriggered;
    case ThreadUpdateTriggered;
}
