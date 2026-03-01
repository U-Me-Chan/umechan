<?php

namespace Ridouchire\RadioScheduler\TracklistGenerators;

use Exception;
use InvalidArgumentException;
use Medoo\Medoo;
use Ridouchire\RadioScheduler\ITracklistGenerator;

class BestEstimateTracklistGenerator implements ITracklistGenerator
{
    public function __construct(
        private Medoo $db
    ) {
    }

    public function build(array $genres = [], int $count = 5, array $exclude_paths = []): array
    {
        if (empty($genres)) {
            throw new InvalidArgumentException('Список жанров не может быть пустым');
        }

        $genres = implode('|', $genres);

        $paths = $this->db->query(
            "WITH sorted_paths AS (SELECT path FROM tracks WHERE path REGEXP :genres ORDER BY estimate DESC, last_playing ASC LIMIT 100), " .
                "random_paths AS (SELECT path FROM sorted_paths ORDER BY RAND() LIMIT :count) " .
                "SELECT * FROM random_paths",
            [
                ':genres' => $genres,
                ':count'  => $count
            ]
        )->fetchAll();

        if (empty($paths)) {
            return [];
        }

        return array_map(fn(array $arr) => $arr['path'], $paths);
    }
}
