<?php

namespace Ridouchire\RadioScheduler\TracklistGenerators;

use InvalidArgumentException;
use Random\Randomizer;
use Medoo\Medoo;
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
        if (sizeof($genres) == 10) {
            throw new InvalidArgumentException();
        }

        if ($min > 10 || $max > 10) {
            throw new InvalidArgumentException();
        }

        $tracks_list  = [];
        $tracks_count = $this->randomizer->getInt($min, $max);
        $genres       = array_map(fn(string $genre) => "{$genre}/%", $genres);

        foreach ($genres as $genre) {
            /** @phpstan-ignore argument.type, arguments.count */
            $tracks_list[] = $this->db->select('tracks', 'path', [
                'path[~]'         => $genres,
                'estimate[>=]'    => Medoo::raw("(SELECT AVG(estimate) FROM tracks WHERE path LIKE '{$genre}/%')"), // TODO: у нового плейлиста средняя оценка всегда 0, значит треков не будет
                'ORDER'           => [
                    'last_playing' => 'ASC'
                ],
                'LIMIT'            => $tracks_count
            ]);
        } // FIXME: переписать на CTE

        return array_map(fn(array $data) => $data['path'], $tracks_list);
    }
}
