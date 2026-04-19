<?php

namespace Ridouchire\RadioScheduler\Tasks\Task\Rules;

use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;
use Ridouchire\RadioScheduler\Tasks\Task\IRule;

class ExactWeekdayRule implements IRule
{
    public function __construct(
        private int $weekday
    ) {
    }

    public function isSatisfiedBy(TaskManagerTickContext $context): bool
    {
        return $context->weekday == $this->weekday ? true : false;
    }
}
