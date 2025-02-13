<?php

namespace Ridouchire\RadioDbImporter\Tracks;

use InvalidArgumentException;
use Medoo\Medoo;
use OutOfBoundsException;
use Ridouchire\RadioDbImporter\Tracks\Track;

class TrackRepository
{
    public function __construct(
        private Medoo $db
    ) {
    }

    public function findOne(array $filters = []): Track
    {
        $conditions = [];

        if (isset($filters['hash'])) {
            $conditions['hash'] = $filters['hash'];
        }

        if (empty($conditions)) {
            throw new InvalidArgumentException();
        }

        $track_data = $this->db->get(
            'tracks',
            [
                'id',
                'artist',
                'title',
                'duration',
                'hash',
                'estimate',
                'first_playing',
                'last_playing',
                'play_count',
                'path'
            ],
            $conditions
        );

        if (!$track_data) {
            throw new OutOfBoundsException();
        }

        return Track::fromArray($track_data);
    }

    public function save(Track $track): int
    {
        $this->db->insert('tracks', $track->toArray());

        return $this->db->id();
    }

    public function update(Track $track)
    {
        $this->db->update('tracks', $track->toArray(), ['id' => $track->getId()]);
    }

    public function delete(Track $track)
    {
        $this->db->delete('tracks', ['id' => $track->getId()]);
    }
}
