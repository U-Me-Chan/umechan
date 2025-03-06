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
        return $this->db->rand('tracks', 'path', [
            'path[~]'      => self::JINGLE_DIR . '/%',
            'estimate[>=]' => 0,
            'LIMIT'        => [0, $count]
        ]);
    }
}
