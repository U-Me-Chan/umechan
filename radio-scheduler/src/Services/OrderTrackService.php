<?php

namespace Ridouchire\RadioScheduler\Services;

use DomainException;
use Medoo\Medoo;
use Monolog\Logger;
use OutOfBoundsException;
use Ridouchire\RadioScheduler\Services\Mpd;
use Ridouchire\RadioScheduler\TracklistGenerators\RandomTracklistGenerator;
use RuntimeException;

class OrderTrackService
{
    public function __construct(
        private Mpd $mpd,
        private Medoo $db,
        private Logger $logger,
        private RandomTracklistGenerator $random_tracklist_generator
    ) {
    }

    public function putTrackInQueue(int $track_id): void
    {
        if ($this->mpd->getQueueCount() >= 20) {
            throw new DomainException();
        }

        $track_path = $this->db->get('tracks', 'path', ['id' => $track_id]);

        if (!$track_path) {
            throw new OutOfBoundsException();
        }

        $result = $this->mpd->addToQueue($track_path, 1);

        if (!$result) {
            throw new RuntimeException();
        }

        $this->logger->info("OrderTrack: ставлю в очередь файл {$track_path}");
    }

    public function putTrackListInQueue(string $genre): void
    {
        if ($this->mpd->getQueueCount() >= 10) {
            throw new DomainException();
        }

        $track_paths = $this->random_tracklist_generator->build([$genre], 10);

        if (empty($track_paths)) {
            throw new RuntimeException();
        }

        array_walk($track_paths, function (string $track_path) {
            $this->logger->info("OrderTrackList: ставлю в очередь файл: {$track_path}");

            $this->mpd->addToQueue($track_path);
        });
    }
}
