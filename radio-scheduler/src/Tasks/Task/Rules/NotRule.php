<?php

namespace Ridouchire\RadioScheduler\Tasks\Task\Rules;

use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;
use Ridouchire\RadioScheduler\Tasks\Task\IRule;

class NotRule implements IRule {
    public function __construct(
        private IRule $rule
    ) {
    }

    public function isSatisfiedBy(TaskManagerTickContext $c): bool
    {
        return !$this->rule->isSatisfiedBy($c);
    }
}
