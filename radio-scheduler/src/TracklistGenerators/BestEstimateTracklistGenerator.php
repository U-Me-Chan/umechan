<?php

namespace Ridouchire\RadioScheduler\TracklistGenerators;

use InvalidArgumentException;
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
        if (empty($genres)) {
            throw new InvalidArgumentException('Список жанров не может быть пустым');
        }

        $genres = array_map(fn(string $genre) => "{$genre}/%", $genres);

        /**
         * @phpstan-ignore-next-line
         */
        $paths =  $this->db->select('tracks', 'path', [
            'path[~]' => $genres,
            'LIMIT'   => $count,
            'ORDER'   => [
                'estimate'     => 'DESC',
                'last_playing' => 'ASC'
            ],
        ]);

        /** @phpstan-ignore nullCoalesce.variable */
        return $paths ?? [];
    }
}
