<?php

namespace Ridouchire\RadioScheduler\Tasks;

use Ridouchire\RadioScheduler\Tasks\Task\IRule;
use Ridouchire\RadioScheduler\Tasks\Task\SequencePosition;

final class Task
{
    /**
     * @param SequencePosition[] $sequence_positions
     */
    public function __construct(
        public readonly IRule $rule,
        public readonly array $sequence_positions,
        public readonly string $name = 'Unnamed'
    ) {
    }
}
