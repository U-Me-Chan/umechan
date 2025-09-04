<?php

namespace Ridouchire\RadioScheduler\TracklistGenerators;

use Random\Randomizer;
use Medoo\Medoo;
use Medoo\Raw;
use Ridouchire\RadioScheduler\ITracklistGenerator;

class AverageEstimateTracklistGenerator implements ITracklistGenerator
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
            'path[~]'         => $genres,
            'estimate[>=]'    => $this->getEstimateSubQueryString($genres),
            'last_playing[<]' => time() - (60 * 60 * 4),
            'ORDER'           => [
                'last_playing' => 'ASC'
            ],
            'LIMIT'            => [0, $tracks_count]
        ]);

        return $tracks_list;
    }

    private function getEstimateSubQueryString(array $genres): Raw
    {
        $query = "(SELECT AVG(estimate) FROM tracks WHERE ";

        $count = sizeof($genres);

        if ($count == 1) {
            $genre = reset($genres);
            $query = "{$query} path LIKE '{$genre}/%')";

            return Medoo::raw($query);
        }

        for ($i = 1; $i <= $count; $i++) {
            $genre = $genres[$i - 1];
            $query .= "path LIKE '{$genre}/%'";

            if ($i !== $count) {
                $query .= ' OR ';
            }
        }

        return Medoo::raw($query . ')');
    }
}
