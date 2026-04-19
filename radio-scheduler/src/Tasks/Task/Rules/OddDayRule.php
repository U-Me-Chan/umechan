<?php

namespace Ridouchire\RadioScheduler\Tasks\Task\Rules;

use Ridouchire\RadioScheduler\Tasks\Task\IRule;
use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;

class OddDayRule implements IRule
{
    public function isSatisfiedBy(TaskManagerTickContext $context): bool
    {
        return $context->day % 2 !== 0 ? true : false;
    }
}
