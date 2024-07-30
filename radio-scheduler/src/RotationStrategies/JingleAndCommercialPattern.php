<?php

namespace Ridouchire\RadioScheduler\RotationStrategies;

use Medoo\Medoo;
use Monolog\Logger;
use Ridouchire\RadioScheduler\IRotation;
use Ridouchire\RadioScheduler\Mpd;

class JingleAndCommercialPattern implements IRotation
{
    public const NAME = 'JingleAndCommercialPattern';

    private const JINGLE_PLS     = 'Jingles';
    private const COMMERCIAL_PLS = 'Commercials';

    public function __construct(
        private Medoo $db,
        private Mpd $mpd,
        private Logger $log
    ) {
    }

    public function execute(): void
    {
        if ($this->mpd->getQueueCount() > 1) {
            $this->log->debug(self::NAME . ': очередь ещё не подошла к концу');

            return;
        }

        $jingle_paths = $this->db->select('tracks', 'path', [
            'path[~]' => self::JINGLE_PLS . '/%',
            'ORDER' => [
                'last_playing' => 'ASC'
            ],
            'LIMIT' => [0, 2]
        ]);

        $commercial_paths = $this->db->select('tracks', 'path', [
            'path[~]' => self::COMMERCIAL_PLS . '/%',
            'ORDER' => [
                'last_playing' => 'ASC'
            ],
            'LIMIT' => [0, 3]
        ]);

        $this->mpd->cropQueue();

        $this->mpd->addToQueue(reset($jingle_paths));

        array_walk($commercial_paths, function (string $path) {
            $this->mpd->addToQueue($path);
        });

        $this->mpd->addToQueue(reset($jingle_paths));
    }
}
