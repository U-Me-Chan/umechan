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

        /**
         * @phpstan-ignore-next-line
         */
        $datas =  $this->db->select('tracks', 'path', [
            'path[~]' => $genres,
            'LIMIT'   => $count,
            'ORDER'   => [
                'estimate'     => 'DESC',
                'last_playing' => 'ASC'
            ],
        ]);

        /** @phpstan-ignore equal.alwaysTrue */
        if ($datas == null) { // Medoo::select может вернуть как пустой массив, так и null
            return [];
        }

        /** @phpstan-ignore deadCode.unreachable */
        return array_map(fn(array $data) => $data['path'], $datas);
    }
}
