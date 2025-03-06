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
        return $this->db->rand('tracks', 'path', [
            'path[~]'      => self::COMMERICIALS_DIR . '/%',
            'estimate[>=]' => 0,
            'LIMIT'        => [0, $count]
        ]);
    }
}
