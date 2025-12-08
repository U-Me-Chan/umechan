<?php

namespace Ridouchire\RadioScheduler;

class TrackSource
{
    public function __construct(
        private array $tags,
        private int $min,
        private int $max,
        private array $sort_rules,
        private array $filter_rules
    ) {
    }
}

$source = new TrackSource(
    ['Foo'],
    8,
    10,
    [
        'last_playing' => 'DESC'
    ],
    [
        'estimate' => [
            'operator' => '>',
            'value'    => 10
        ]
    ]
);
