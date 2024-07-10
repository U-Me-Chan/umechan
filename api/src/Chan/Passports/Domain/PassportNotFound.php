<?php

namespace PK\Domain\Exceptions;

use Exception;

class PassportNotFound extends Exception
{
    protected $message = 'Паспорт не найден';
}
