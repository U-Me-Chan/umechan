<?php

namespace PK\Chan\Boards\Domain;

class BoardNotFoundException extends \Exception
{
    protected $message = 'Доска не найдена';
}
