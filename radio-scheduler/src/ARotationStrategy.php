<?php

namespace Ridouchire\RadioScheduler;

use Monolog\Logger;
use Ridouchire\RadioScheduler\Services\Mpd;

abstract class ARotationStrategy
{
    public const NAME = 'strategy';

    public function __construct(
        private Mpd $mpd,
        private Logger $logger
    ) {
    }

    final public function execute(array $genres): void
    {
        $track_paths = $this->buildTrackList($genres);

        array_walk($track_paths, function (string $track_path) {
            $this->logger->info(self::NAME . ": ставлю в очередь {$track_path}");

            if (!$this->mpd->addToQueue($track_path)) {
                $this->logger->error(self::NAME . ": ошибка постановки в очередь {$track_path}");
            }
        });
    }

    abstract protected function buildTrackList(array $genres): array;
}
