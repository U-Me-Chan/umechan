<?php

namespace Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators;

use Medoo\Medoo;
use Ridouchire\RadioScheduler\Tasks\Services\ITracklistGenerator;

class RandomTracklistGenerator implements ITracklistGenerator
{
    public function __construct(
        private Medoo $db
    ) {
    }

    public function build(array $genres = [], int $count = 1, array $exclude_ids = []): array
    {
        $genres = array_map(fn(string $genre) => "{$genre}/%", $genres);

        $conditions = [
            'path[~]'      => $genres,
            'estimate[>=]' => 0,
            'LIMIT'        => $count
        ];

        if (!empty($exclude_ids)) {
            $conditions['id[!]'] = $exclude_ids;
        }

        return $this->db->rand('tracks', 'path', $conditions);
    }
}
