<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\NotRule;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\OddDayRule;
use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;

class NotRuleTest extends TestCase
{
    #[Test]
    public function attemptTrueCondition(): void
    {
        $rule = new NotRule(new OddDayRule());

        $this->assertTrue($rule->isSatisfiedBy(new TaskManagerTickContext(1, 2, 1, 1, 2020)));
    }

    #[Test]
    public function attemptFalseCondition(): void
    {
        $rule = new NotRule(new OddDayRule());

        $this->assertFalse($rule->isSatisfiedBy(new TaskManagerTickContext(1, 1, 1, 1, 2020)));
    }
}
