<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\ExactWeekdayRule;
use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;

final class ExactWeekdayRuleTest extends TestCase
{
    #[Test]
    public function attemptValidHour(): void
    {
        $this->assertTrue(
            new ExactWeekdayRule(1)
                ->isSatisfiedBy(new TaskManagerTickContext(1, 1, 1, 1, 2020))
        );
    }
}
