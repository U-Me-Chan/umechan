<?php

namespace Ridouchire\RadioScheduler\TracklistGenerators;

use Medoo\Medoo;
use Ridouchire\RadioScheduler\ITracklistGenerator;

class BestEstimateTracklistGenerator implements ITracklistGenerator
{
    public function __construct(
        private Medoo $db
    ) {
    }

    public function build(array $genres = [], int $count = 5): array
    {
        $genres = array_map(fn(string $genre) => "{$genre}/%", $genres);

        $datas =  $this->db->select('tracks', 'path', [
            'path[~]'      => $genres,
            'ORDER' => [
                'estimate'     => 'DESC',
                'last_playing' => 'ASC'
            ],
            'LIMIT'        => [0, $count]
        ]);

        return array_map(fn(array $data) => $data['path'], $datas);
    }
}
