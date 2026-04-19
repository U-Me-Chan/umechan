<?php

namespace Ridouchire\RadioScheduler\Tasks\Task\Rules;

use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;
use Ridouchire\RadioScheduler\Tasks\Task\IRule;

class ExactHourRule implements IRule
{
    public function __construct(
        public readonly int $hour
    ) {
    }

    public function isSatisfiedBy(TaskManagerTickContext $context): bool
    {
        return $context->hour == $this->hour ? true : false;
    }
}
