<?php

namespace PK\Exceptions\Http;

class NotFound extends \Exception
{
    protected $message = 'Нет такого ресурса'; // @phpstan-ignore missingType.property
}
