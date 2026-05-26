<?php

namespace PK\Events;

use PK\Events\Message;

interface MessageBroker
{
    public function publish(Message $message): void;
}
