<?php

namespace Ridouchire\RadioScheduler\Tasks\Task\Rules;

use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;
use Ridouchire\RadioScheduler\Tasks\Task\IRule;

class AndRule implements IRule {
    public function __construct(
        private array $rules
    ) {
    }

    public function isSatisfiedBy(TaskManagerTickContext $c): bool
    {
        foreach ($this->rules as $r) {
            if (!$r->isSatisfiedBy($c)) {
                return false;
            }
        }

        return true;
    }
}
