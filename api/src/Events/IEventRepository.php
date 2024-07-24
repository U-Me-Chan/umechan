<?php

namespace PK\Events;

use PK\Base\IRepository;
use PK\Events\Event\Event;

interface IEventRepository extends IRepository
{
    public function findOne(array $filters = []): Event;
    public function save(Event $event);
}
