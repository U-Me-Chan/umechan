<?php

namespace Ridouchire\RadioScheduler;

use Medoo\Medoo;

class Jingles
{
    private const JINGLE_DIR = 'Jingles';

    public function __construct(
        private Medoo $db
    ) {
    }

    public function getJingles(int $count = 2): array
    {
        return $this->db->get('tracks', 'path', [
            'path[~]' => self::JINGLE_DIR . '/%',
            'ORDER'   => [
                'last_playing' => 'ASC'
            ],
            'LIMIT' => [0, $count]
        ]);
    }
}
