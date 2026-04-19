<?php

namespace Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators;

use Medoo\Medoo;
use Ridouchire\RadioScheduler\Tasks\Services\ITracklistGenerator;

class NewOrLongStandingTracklistGenerator implements ITracklistGenerator
{
    public function __construct(
        private Medoo $db
    ) {
    }

    /**
     * @return string[]
     */
    public function build(array $genres = [], int $min = 4, int $max = 8, array $exclude_paths = []): array
    {
        $tracks_list  = [];
        $tracks_count = random_int($min, $max);
        $genres       = array_map(fn(string $genre) => "{$genre}/%", $genres);

        $conditions = [
            'path[~]' => $genres,
            'ORDER'   => [
                'last_playing' => 'ASC'
            ],
            'LIMIT' => $tracks_count
        ];

        if (!empty($exclude_paths)) {
            $conditions['path[!]'] = $exclude_paths;
        }

        /** @phpstan-ignore-next-line */
        $tracks_list = $this->db->select('tracks', 'path', $conditions);

        /** @phpstan-ignore nullCoalesce.variable */
        return $tracks_list ?? [];
    }
}
