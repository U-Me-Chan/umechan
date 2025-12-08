<?php

namespace Ridouchire\RadioScheduler;

use InvalidArgumentException;
use RuntimeException;
use Monolog\Logger;
use Ridouchire\RadioScheduler\Services\Mpd;
use Ridouchire\RadioScheduler\TracklistGenerators\AverageEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\BestEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\NewOrLongStandingTracklistGenerator;
use Ridouchire\RadioScheduler\TracklistGenerators\RandomTracklistGenerator;

class RotationMaster
{
    private array $hours = [];
    private array $weekdays = [];
    private array $days = [];

    public function __construct(
        private Logger $log,
        private Mpd $mpd,
        private AverageEstimateTracklistGenerator $average_estimate_tracklist_generator,
        private BestEstimateTracklistGenerator $best_estimate_tracklist_generator,
        private NewOrLongStandingTracklistGenerator $new_or_long_standing_tracklist_generator,
        private RandomTracklistGenerator $random_tracklist_generator
    ) {
        $this->hours      = array_fill(0, 24, []);
        $this->weekdays   = array_fill(1, 7, array_fill(0, 23, []));
        $this->days       = array_fill(1, 31, array_fill(0, 23, []));
    }

    private function sendTrackListToMpd(array $track_list): void
    {
        array_walk($track_list, function (string $track_path) {
            $this->log->info("RotationMaster: Cтавлю в очередь {$track_path}");

            if (!$this->mpd->addToQueue($track_path)) {
                $this->log->error("RotationMaster: ошибка постановки в очередь {$track_path}");
            }
        });
    }

    private function runFirstStrategyFromList(array $strategies): bool
    {
        foreach ($strategies as $strategy_data) {
            $genres = $strategy_data['genres'];

            match($strategy_data['type']) {
                RotationType::AverageOrAboveEstimateAndLongStanding => $this->sendTrackListToMpd($this->average_estimate_tracklist_generator->build($genres, 8, 12)),
                RotationType::BestEstimate                          => $this->sendTrackListToMpd($this->best_estimate_tracklist_generator->build($genres, 10)),
                RotationType::NewOrLongStangind                     => $this->sendTrackListToMpd($this->new_or_long_standing_tracklist_generator->build($genres, 8, 12)),
                RotationType::Random                                => $this->sendTrackListToMpd($this->random_tracklist_generator->build($genres, 10))
            };

            return true;
        }

        return false;
    }

    public function execute(?int $timestamp = null): void
    {
        $hour    = (int) date('G', $timestamp);
        $day     = (int) date('d', $timestamp);
        $weekday = (int) date('w', $timestamp);

        if (isset($this->days[$day]) &&
            !empty($this->days[$day]) &&
            isset($this->days[$day][$hour]) &&
            !empty($this->days[$day][$hour]) &&
            $this->runFirstStrategyFromList($this->days[$day][$hour])
        ) {
            return;
        }

        if (isset($this->weekdays[$weekday]) &&
            !empty($this->weekdays[$weekday]) &&
            isset($this->weekdays[$weekday][$hour]) &&
            !empty($this->weekdays[$weekday][$hour]) &&
            $this->runFirstStrategyFromList($this->weekdays[$weekday][$hour])
        ) {
            return;
        }

        if (!isset($this->hours[$hour]) || empty($this->hours[$hour])) {
            throw new RuntimeException('Нет стратегии ротации на данный час');
        }

        if ($this->runFirstStrategyFromList($this->hours[$hour])) {
            return;
        }

        throw new RuntimeException('Нет стратегии ротации на данный час');
    }

    public function addStrategyByHour(int $hour, RotationType $rotation_type, array $genres, bool $is_high_priority = false): void
    {
        if (!$this->isValidHour($hour)) {
            throw new InvalidArgumentException();
        }

        if ($is_high_priority) {
            array_unshift($this->hours[$hour], [
                'type'   => $rotation_type,
                'genres' => $genres
            ]);
        } else {
            array_push($this->hours[$hour], [
                'type'   => $rotation_type,
                'genres' => $genres
            ]);
        }
    }

    public function addStrategyByPeriod(int $from, int $to, RotationType $rotation_type, array $genres, bool $is_high_priority = false): void
    {
        if (!$this->isValidHour($from)) {
            throw new InvalidArgumentException();
        }

        if (!$this->isValidHour($to)) {
            throw new InvalidArgumentException();
        }

        if ($to < $from) {
            throw new InvalidArgumentException();
        }

        if ($from == $to) {
            throw new InvalidArgumentException();
        }

        $hours = range($from, $to);

        foreach ($hours as $hour) {
            if ($is_high_priority) {
                array_unshift($this->hours[$hour], [
                    'type'   => $rotation_type,
                    'genres' => $genres
                ]);
            } else {
                array_push($this->hours[$hour], [
                    'type'   => $rotation_type,
                    'genres' => $genres
                ]);
            }
        }
    }

    public function addStrategyByDayAndHour(int $day, int $hour, RotationType $rotation_type, array $genres, bool $is_high_priority = false): void
    {
        if ($is_high_priority) {
            array_unshift($this->days[$day][$hour], [
                'type'   => $rotation_type,
                'genres' => $genres
            ]);
        } else {
            array_push($this->days[$day][$hour], [
                'type'   => $rotation_type,
                'genres' => $genres
            ]);
        }
    }

    public function addStrategyByWeekdayAndHour(int $hour, int $weekday, RotationType $rotation_type, array $genres, bool $is_high_priority = false): void
    {
        if ($is_high_priority) {
            array_unshift($this->weekdays[$weekday][$hour], [
                'type'   => $rotation_type,
                'genres' => $genres
            ]);
        } else {
            array_push($this->weekdays[$weekday][$hour], [
                'type'   => $rotation_type,
                'genres' => $genres
            ]);
        }
    }

    private function isValidHour(int $hour): bool
    {
        return $hour < 0 || $hour > 23 ? false : true;
    }
}
