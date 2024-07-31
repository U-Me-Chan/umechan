<?php

namespace Ridouchire\RadioScheduler;

use Medoo\Medoo;

class Commercials
{
    private const COMMERICIALS_DIR = 'Commercials';

    public function __construct(
        private Medoo $db
    ) {
    }

    public function getCommercials(int $count = 3): array
    {
        return $this->db->select('tracks', 'path', [
            'path[~]' => self::COMMERICIALS_DIR . '/%',
            'ORDER'   => [
                'last_playing' => 'ASC'
            ],
            'LIMIT' => [0, $count]
        ]);
    }
}
