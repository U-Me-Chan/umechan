<?php

namespace Ridouchire\RadioScheduler\Tasks\Task\Rules;

use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;
use Ridouchire\RadioScheduler\Tasks\Task\IRule;

class ExactHoursMatchRule implements IRule
{
    public function __construct(
        public readonly int $start_hour,
        public readonly int $end_hour
    ) {
    }

    public function isSatisfiedBy(TaskManagerTickContext $context): bool
    {
        return
            $context->hour >= $this->start_hour &&
            $context->hour <= $this->end_hour
            ? true
            : false;
    }
}
