<?php

namespace Ridouchire\RadioScheduler\Tasks\Services;

use RuntimeException;
use Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators\AverageEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators\BestEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators\NewOrLongStandingTracklistGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators\RandomTracklistGenerator;
use Ridouchire\RadioScheduler\Tasks\Task\SequencePosition;
use Ridouchire\RadioScheduler\Tasks\Task\TrackSources\DirectorySource;
use Ridouchire\RadioScheduler\Tasks\Task\TracklistGeneratorType;

class SequenceGenerator
{
    public function __construct(
        private AverageEstimateTracklistGenerator $average_estimate_tracklist_generator,
        private BestEstimateTracklistGenerator $best_estimate_tracklist_generator,
        private NewOrLongStandingTracklistGenerator $new_or_long_standing_tracklist_generator,
        private RandomTracklistGenerator $random_tracklist_generator
    ) {
    }

    /**
     * @param SequencePosition[] $sequence_positions
     *
     * @return string[]
     */
    public function generate(array $sequence_positions): array
    {
        $tracks_list = [];

        foreach ($sequence_positions as $sequence_position) {
            /** @phpstan-ignore instanceof.alwaysTrue */
            if (!$sequence_position instanceof SequencePosition) {
                throw new RuntimeException();
            }

            $tracks_list = array_merge(
                $tracks_list,
                $this->generateFromSequencePosition($sequence_position)
            );
        }

        return $tracks_list;
    }

    private function generateFromSequencePosition(SequencePosition $sequence_position): array
    {
        $tracks_list = [];

        foreach ($sequence_position->sources as $source) {
            if ($source instanceof DirectorySource) {
                $tracks_list = array_merge(
                    $tracks_list,
                    $this->generateTracklistFromDirectorySource(
                        $source,
                        $sequence_position->gentype,
                        $sequence_position->min_count,
                        $sequence_position->max_count
                    )
                );
            } else {
                throw new RuntimeException();
            }
        }

        return $tracks_list;
    }

    private function generateTracklistFromDirectorySource(
        DirectorySource $source,
        TracklistGeneratorType $gentype,
        int $min_count,
        int $max_count
    ): array
    {
        $tracks_list = [];

        switch ($gentype) {
            case TracklistGeneratorType::smart:
                $avg_tracks_list = $this->average_estimate_tracklist_generator->build(
                    $source->dirs,
                    $min_count,
                    $max_count
                );
                $bst_tracks_list = $this->best_estimate_tracklist_generator->build(
                    $source->dirs,
                    $min_count,
                    $avg_tracks_list
                );
                $new_tracks_list = $this->new_or_long_standing_tracklist_generator->build(
                    $source->dirs,
                    $min_count,
                    $max_count,
                    array_merge($avg_tracks_list, $bst_tracks_list)
                );

                $tracks_list = array_merge($avg_tracks_list, $bst_tracks_list, $new_tracks_list);

                break;
            case TracklistGeneratorType::average:
                $tracks_list = array_merge(
                    $tracks_list,
                    $this->average_estimate_tracklist_generator->build(
                        $source->dirs,
                        $min_count,
                        $max_count
                    )
                );

                break;
            case TracklistGeneratorType::best:
                $tracks_list = array_merge(
                    $tracks_list,
                    $this->best_estimate_tracklist_generator->build(
                        $source->dirs,
                        $min_count
                    )
                );

                break;
            case TracklistGeneratorType::new_or_long_standing:
                $tracks_list = array_merge(
                    $tracks_list,
                    $this->new_or_long_standing_tracklist_generator->build(
                        $source->dirs,
                        $min_count,
                        $max_count
                    )
                );

                break;
            case TracklistGeneratorType::random:
                $tracks_list = array_merge(
                    $tracks_list,
                    $this->random_tracklist_generator->build(
                        $source->dirs,
                        $min_count
                    )
                );

                break;
            default:
                throw new RuntimeException();
        }

        return $tracks_list;
    }
}
