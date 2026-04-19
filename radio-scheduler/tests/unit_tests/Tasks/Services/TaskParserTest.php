<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Tasks\Services\TaskParser;
use Ridouchire\RadioScheduler\Tasks\Task;
use Ridouchire\RadioScheduler\Tasks\Task\TracklistGeneratorType;
use Ridouchire\RadioScheduler\Tasks\Task\TrackSources\DirectorySource;
use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;

class TaskParserTest extends TestCase
{
    private TaskParser $task_parser;

    public function setUp(): void
    {
        $this->task_parser = new TaskParser();
    }

    #[Test]
    #[TestWith(['tasks: foo'])]
    #[TestWith([''])]
    public function attemptParseFromStringInvalidFormat(string $yaml): void
    {
        $this->expectException(RuntimeException::class);
        $this->task_parser->parseFromString($yaml);
    }

    #[Test]
    public function parseValidYaml(): void
    {
        $yaml = <<<'YAML'
- name: 'foo'
  hours:
    - 9
    - 10
  weekdays:
    - 1
    - 2
  odd_day: true
  positions:
    - gentype: random
      sources:
        - 'dir:Jingles'
      count:
        max: 1
    - gentype: smart
      sources:
        - 'dir:Pop'
        - 'dir:Pop Ru'
      count:
        min: 7
        max: 9
YAML;

        $tasks = $this->task_parser->parseFromString($yaml);

        $this->assertCount(1, $tasks);

        $this->assertInstanceOf(Task::class, $tasks[0]);

        /** @var Task */
        $task = $tasks[0];

        $this->assertEquals('foo', $task->name);

        $this->assertTrue(
            $task->rule->isSatisfiedBy(
                new TaskManagerTickContext(9, 3, 1, 1, 2020)
            )
        );
        $this->assertTrue(
            $task->rule->isSatisfiedBy(
                new TaskManagerTickContext(10, 3, 2, 1, 2020)
            )
        );
        $this->assertFalse(
            $task->rule->isSatisfiedBy(
                new TaskManagerTickContext(9, 2, 1, 1, 2020)
            )
        );

        $this->assertCount(2, $task->sequence_positions);

        $this->assertEquals(
            TracklistGeneratorType::random->name,
            $task->sequence_positions[0]->gentype->name
        );

        $this->assertEquals(1, $task->sequence_positions[0]->min_count);
        $this->assertEquals(1, $task->sequence_positions[0]->max_count);

        $this->assertInstanceOf(DirectorySource::class, $task->sequence_positions[0]->sources[0]);
        $this->assertEquals(['Jingles'], $task->sequence_positions[0]->sources[0]->dirs);

        $this->assertEquals(
            TracklistGeneratorType::smart->name,
            $task->sequence_positions[1]->gentype->name
        );

        $this->assertEquals(7, $task->sequence_positions[1]->min_count);
        $this->assertEquals(9, $task->sequence_positions[1]->max_count);

        $this->assertInstanceOf(DirectorySource::class, $task->sequence_positions[1]->sources[0]);
        $this->assertEquals(['Pop', 'Pop Ru'], $task->sequence_positions[1]->sources[0]->dirs);
    }
}
