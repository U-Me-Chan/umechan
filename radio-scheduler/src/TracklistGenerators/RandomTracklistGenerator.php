<?php

namespace Ridouchire\RadioScheduler\TracklistGenerators;

use Medoo\Medoo;
use Ridouchire\RadioScheduler\ITracklistGenerator;

class RandomTracklistGenerator implements ITracklistGenerator
{
    public function __construct(
        private Medoo $db
    ) {
    }

    public function build(array $genres = [], int $count = 1): array
    {
        $genres = array_map(fn(string $genre) => "{$genre}/%", $genres);

        return $this->db->rand('tracks', 'path', [
            'path[~]'      => $genres,
            'estimate[>=]' => 0,
            'LIMIT'        => [0, $count]
        ]);
    }
}
