<?php

namespace Ridouchire\RadioScheduler\Services;

use DomainException;
use Medoo\Medoo;
use Monolog\Logger;
use OutOfBoundsException;
use Ridouchire\RadioScheduler\Services\Mpd;
use Ridouchire\RadioScheduler\TracklistGenerators\AverageEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\BestEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\NewOrLongStandingTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\RandomTracklistGenerator;
use RuntimeException;

class OrderTrackService
{
    public function __construct(
        private Mpd $mpd,
        private Medoo $db,
        private Logger $logger,
        private RandomTracklistGenerator $random_tracklist_generator,
        private NewOrLongStandingTracklistGenerator $new_or_long_standing_tracklist_generator,
        private AverageEstimateTracklistGenerator $average_estimate_tracklist_generator,
        private BestEstimateTracklistGenerator $best_estimate_tracklist_generator
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

    public function putTrackListInQueue(string $genre, string $rotation = 'random'): void
    {
        if ($this->mpd->getQueueCount() > 20) {
            throw new DomainException();
        }

        switch($rotation) {
            case 'smart':
                $new_track_paths = $this->new_or_long_standing_tracklist_generator->build([$genre], 4, 8);
                $avg_track_paths = $this->average_estimate_tracklist_generator->build([$genre], 4, 8);
                $bst_track_paths = $this->best_estimate_tracklist_generator->build([$genre], 4);
                $track_paths     = array_merge($new_track_paths, $avg_track_paths, $bst_track_paths);

                shuffle($track_paths);
            case 'random':
            default:
                $track_paths = $this->random_tracklist_generator->build([$genre], 10);
                break;
        }

        if (empty($track_paths)) {
            throw new RuntimeException();
        }

        array_walk($track_paths, function (string $track_path) {
            $this->logger->info("OrderTrackList: ставлю в очередь файл: {$track_path}");

            $this->mpd->addToQueue($track_path);
        });
    }
}
