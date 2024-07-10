<?php

namespace PK\Domain\Exceptions;

class PostNotFound extends \Exception
{
    protected $message = 'Пост не найден';
}
