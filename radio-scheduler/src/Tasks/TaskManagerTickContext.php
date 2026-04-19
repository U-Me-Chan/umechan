<?php

namespace Ridouchire\RadioScheduler\Tasks;

final readonly class TaskManagerTickContext
{
    public function __construct(
        public int $hour,
        public int $day,
        public int $weekday,
        public int $month,
        public int $year
    ) {
    }
}
