<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\AndRule;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\ExactHourRule;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\ExactWeekdayRule;
use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;

class AndRuleTest extends TestCase
{
    #[Test]
    public function attemptTrueConditions(): void
    {
        $rule = new AndRule([
            new ExactHourRule(0),
            new ExactWeekdayRule(1)
        ]);

        $this->assertTrue($rule->isSatisfiedBy(new TaskManagerTickContext(0, 1, 1, 1, 2020)));
    }

    #[Test]
    public function attemptFalseConditions(): void
    {
        $rule = new AndRule([
            new ExactHourRule(1),
            new ExactWeekdayRule(1)
        ]);

        $this->assertFalse($rule->isSatisfiedBy(new TaskManagerTickContext(0, 1, 1, 1, 2020)));
    }
}
