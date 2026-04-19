<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\ExactHourRule;
use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;

final class ExactHourRuleTest extends TestCase
{
    #[Test]
    public function attemptValidHour(): void
    {
        $this->assertTrue(
            new ExactHourRule(1)
                ->isSatisfiedBy(new TaskManagerTickContext(1, 1, 1, 1, 2020))
        );
    }
}
