<?php

namespace Ridouchire\RadioScheduler\Tasks\Services;

use Exception;
use Monolog\Logger;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\Tasks\TaskManagerTickContext;
use Ridouchire\RadioScheduler\Tasks\Task;

final class TaskManager
{
    public const EXIT_CODE_NO_ERROR = 0;
    public const EXIT_CODE_ERR_NO_TASKS = -1;
    public const EXIT_CODE_ERR_NO_TASK_ON_TIME = -2;

    /** @var Task[] */
    private array $list = [];

    private ?string $current_task_name;

    public function __construct(
        private SequenceGenerator $sequence_generator,
        private Mpd $mpd,
        private Logger $logger
    ) {
    }

    public function add(Task $task): void
    {
        array_push($this->list, $task);
    }

    public function tick(?int $timestamp = null): int
    {
        if (empty($this->list)) {
            return self::EXIT_CODE_ERR_NO_TASKS;
        }

        $context = new TaskManagerTickContext(
            (int) date('G', $timestamp),
            (int) date('j', $timestamp),
            (int) date('N', $timestamp),
            (int) date('n', $timestamp),
            (int) date('o', $timestamp)
        );

        foreach ($this->list as $task) {
            if ($task->rule->isSatisfiedBy($context)) {
                $this->logger->info('TaskManager: пришло время задачи под именем ' . $task->name);

                $this->current_task_name = $task->name;

                $tracks_list = $this->sequence_generator->generate($task->sequence_positions);

                $this->addTracksToQueue($tracks_list);

                return self::EXIT_CODE_NO_ERROR;
            }
        }

        $this->current_task_name = null;

        return self::EXIT_CODE_ERR_NO_TASK_ON_TIME;
    }

    public function getCurrentTaskName(): ?string
    {
        return $this->current_task_name;
    }

    public function cropQueue(int $timestamp): bool
    {
        $time = date('Gis', $timestamp);

        switch ($time) {
            case '00000':
            case '60000':
            case '90000':
            case '190000':
                return $this->mpd->cropQueue();
            default:
                throw new Exception('Ещё не время');
        }
    }

    private function addTracksToQueue(array $paths): void
    {
        array_walk($paths, function (string $path) {
            $this->logger->info('TaskManager: ставлю в очередь ' . $path);

            if (!$this->mpd->addToQueue($path)) {
                $this->logger->error('TaskManager: ошибка поставки в очередь: ' . $path);
            }
        });
    }
}
