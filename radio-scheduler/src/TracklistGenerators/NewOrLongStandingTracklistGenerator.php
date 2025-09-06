<?php

namespace Ridouchire\RadioScheduler\TracklistGenerators;

use Random\Randomizer;
use Medoo\Medoo;
use Ridouchire\RadioScheduler\ITracklistGenerator;

class NewOrLongStandingTracklistGenerator implements ITracklistGenerator
{
    public function __construct(
        private Medoo $db,
        private Randomizer $randomizer
    ) {
    }

    public function build(array $genres = [], int $min = 4, int $max = 8): array
    {
        $tracks_list  = [];
        $tracks_count = $this->randomizer->getInt($min, $max);
        $genres       = array_map(fn(string $genre) => "{$genre}/%", $genres);

        /** @phpstan-ignore argument.type, arguments.count */
        $tracks_list = $this->db->select('tracks', 'path', [
            'path[~]'        => $genres,
            'ORDER'          => [
                'last_playing' => 'ASC',
            ],
            'LIMIT' => [0, $tracks_count]
        ]);

        return $tracks_list;
    }
}
