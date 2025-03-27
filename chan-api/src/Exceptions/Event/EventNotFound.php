<?php

namespace PK\Exceptions\Event;

class EventNotFound extends \Exception
{
    protected $message = "Событие не найдено"; // @phpstan-ignore missingType.property
}
