<?php

namespace PK\Events;

abstract class AbstractEventCallback
{
    public function __construct(
        protected IEventRepository $event_repo
    ) {
    }
}
