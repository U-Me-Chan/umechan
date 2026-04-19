<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\ExactWeekdayRule;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\OrRule;
use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;

class OrRulesTest extends TestCase
{
    #[Test]
    public function attemptTrueConditions(): void
    {
        $rule = new OrRule([
            new ExactWeekdayRule(1),
            new ExactWeekdayRule(2)
        ]);

        $this->assertTrue($rule->isSatisfiedBy(new TaskManagerTickContext(1, 1, 1, 1, 2020)));
    }

    #[Test]
    public function attemptFalseConditions(): void
    {
        $rule = new OrRule([
            new ExactWeekdayRule(1),
            new ExactWeekdayRule(2)
        ]);

        $this->assertFalse($rule->isSatisfiedBy(new TaskManagerTickContext(1, 1, 3, 1, 2020)));
    }
}
