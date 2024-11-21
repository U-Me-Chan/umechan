<?php

namespace PK\Domain;

enum EventType: string
{
    case PostCreated           = 'PostCreated';
    case PostDeleted           = 'PostDeleted';
    case BoardUpdateTriggered  = 'BoardUpdateTriggered';
    case ThreadUpdateTriggered = 'ThreadUpdateTriggered';
}
