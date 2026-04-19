<?php

namespace Ridouchire\RadioScheduler\Tasks\Task;

use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;

interface IRule
{
    public function isSatisfiedBy(TaskManagerTickContext $context): bool;
}
