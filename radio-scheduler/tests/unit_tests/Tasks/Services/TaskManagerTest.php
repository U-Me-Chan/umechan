<?php

use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\Tasks\Services\SequenceGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TaskManager;
use Ridouchire\RadioScheduler\Tasks\Task;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\NotRule;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\OddDayRule;

final class TaskManagerTest extends TestCase
{
    /** @var MockObject|SequenceGenerator */
    private MockObject|SequenceGenerator $sequence_generator;
    /** @var MockObject|Mpd */
    private MockObject|Mpd $mpd;
    /** @var MockObject|Logger */
    private MockObject|Logger $logger;

    private TaskManager $task_manager;

    public function setUp(): void
    {
        $this->sequence_generator = $this->createMock(SequenceGenerator::class);
        $this->mpd = $this->createMock(Mpd::class);
        $this->logger = $this->createMock(Logger::class);

        $this->task_manager = new TaskManager(
            $this->sequence_generator,
            $this->mpd,
            $this->logger
        );
    }

    #[Test]
    public function attemptRunWithoutTasks(): void
    {
        $this->assertEquals(
            TaskManager::EXIT_CODE_ERR_NO_TASKS,
            $this->task_manager->tick()
        );
    }

    #[Test]
    public function attemptRunWithoutTasksOnNowTime(): void
    {
        $this->task_manager->add(new Task(new OddDayRule(), []));

        $this->assertEquals(
            TaskManager::EXIT_CODE_ERR_NO_TASK_ON_TIME,
            $this->task_manager->tick(1775288734)
        );
    }

    #[Test]
    public function attemptRunWithTaskOnNowTime(): void
    {
        $this->task_manager->add(new Task(new NotRule(new OddDayRule()), []));

        $this->logger->expects($this->exactly(4))
            ->method('info');

        $this->logger->expects($this->once())
            ->method('error');

        $this->sequence_generator->expects($this->once())
            ->method('generate')->willReturn([
                'Dir/1.mp3',
                'Dir/2.mp3',
                'Dir/3.mp3'
            ]);

        $this->mpd->expects($this->exactly(3))
            ->method('addToQueue')
            ->willReturn(true, true, false);

        $this->assertEquals(
            TaskManager::EXIT_CODE_NO_ERROR,
            $this->task_manager->tick(1775288734)
        );
    }
}
