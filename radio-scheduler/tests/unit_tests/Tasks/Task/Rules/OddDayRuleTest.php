<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\OddDayRule;
use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;

final class OddDayRuleTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    public function test(int $day, bool $expected): void
    {
        $this->assertEquals(
            $expected,
            new OddDayRule()->isSatisfiedBy(new TaskManagerTickContext(0, $day, 1, 1, 2002))
        );
    }

    public static function dataProvider(): array
    {
        return [
            [1, true],
            [2, false],
            [22, false],
            [30, false],
            [31, true]
        ];
    }
}
