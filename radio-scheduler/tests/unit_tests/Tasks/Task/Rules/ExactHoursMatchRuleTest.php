<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\ExactHoursMatchRule;
use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;

final class ExactHoursMatchRuleTest extends TestCase
{
    #[Test]
    public function attemptValidValues(): void
    {
        $this->assertTrue(
            new ExactHoursMatchRule(1, 6)
                ->isSatisfiedBy(new TaskManagerTickContext(1, 1, 1, 1, 2020))
        );
    }

    #[Test]
    public function attemptInvalidValues(): void
    {
        $this->assertFalse(
            new ExactHoursMatchRule(1, 6)
                ->isSatisfiedBy(new TaskManagerTickContext(0, 1, 1, 1, 2020))
        );
    }
}
