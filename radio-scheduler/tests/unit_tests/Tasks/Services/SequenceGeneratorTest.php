<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Tasks\Services\SequenceGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators\AverageEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators\BestEstimateTracklistGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators\NewOrLongStandingTracklistGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TracklistGenerators\RandomTracklistGenerator;
use Ridouchire\RadioScheduler\Tasks\Task\SequencePosition;
use Ridouchire\RadioScheduler\Tasks\Task\TracklistGeneratorType;
use Ridouchire\RadioScheduler\Tasks\Task\TrackSources\DirectorySource;

class SequenceGeneratorTest extends TestCase
{
    private MockObject|AverageEstimateTracklistGenerator $average_estimate_tracklist_generator;
    private MockObject|BestEstimateTracklistGenerator $best_estimate_tracklist_generator;
    private MockObject|NewOrLongStandingTracklistGenerator $new_or_long_standing_tracklist_generator;
    private MockObject|RandomTracklistGenerator $random_tracklist_generator;

    private SequenceGenerator $sequence_generator;

    public function setUp(): void
    {
        $this->average_estimate_tracklist_generator = $this->createMock(AverageEstimateTracklistGenerator::class);
        $this->best_estimate_tracklist_generator = $this->createMock(BestEstimateTracklistGenerator::class);
        $this->new_or_long_standing_tracklist_generator = $this->createMock(NewOrLongStandingTracklistGenerator::class);
        $this->random_tracklist_generator = $this->createMock(RandomTracklistGenerator::class);

        $this->sequence_generator = new SequenceGenerator(
            $this->average_estimate_tracklist_generator,
            $this->best_estimate_tracklist_generator,
            $this->new_or_long_standing_tracklist_generator,
            $this->random_tracklist_generator
        );
    }

    #[Test]
    public function attemptGenerateWithoutPositions(): void
    {
        $this->assertEmpty($this->sequence_generator->generate([]));
    }

    #[Test]
    public function attemptGenerateWithSimpleGenerators(): void
    {
        $this->random_tracklist_generator
            ->expects($this->once())
            ->method('build')
            ->willReturnCallback(function (array $dirs, int $count) {
                $this->assertContains('Jingles', $dirs);
                $this->assertCount(1, $dirs);
                $this->assertEquals(1, $count);

                return ['Jingles/1.mp3'];
            });
        $this->best_estimate_tracklist_generator
            ->expects($this->once())
            ->method('build')
            ->willReturnCallback(function (array $dirs, int $count) {
                $this->assertContains('Pop', $dirs);
                $this->assertCount(1, $dirs);
                $this->assertEquals(3, $count);

                return ['Pop/one.mp3', 'Pop/two.mp3', 'Pop/three.mp3'];
            });
        $this->average_estimate_tracklist_generator
            ->expects($this->once())
            ->method('build')
            ->willReturnCallback(function (array $dirs, int $min_count, int $max_count) {
                $this->assertContains('Pop', $dirs);
                $this->assertCount(1, $dirs);
                $this->assertEquals(3, $min_count);
                $this->assertEquals(5, $max_count);

                return ['Pop/four.mp3', 'Pop/five.mp3', 'Pop/six.mp3'];
            });
        $this->new_or_long_standing_tracklist_generator
            ->expects($this->once())
            ->method('build')
            ->willReturnCallback(function (array $dirs, int $min_count, int $max_count) {
                $this->assertContains('Pop', $dirs);
                $this->assertCount(1, $dirs);
                $this->assertEquals(3, $min_count);
                $this->assertEquals(5, $max_count);

                return ['Pop/seven.mp3', 'Pop/eight.mp3', 'Pop/nine.mp3'];
            });


        $tracks_list = $this->sequence_generator->generate([
            new SequencePosition(
                TracklistGeneratorType::random,
                [
                    new DirectorySource(['Jingles'])
                ],
                1,
                1
            ),
            new SequencePosition(
                TracklistGeneratorType::best,
                [
                    new DirectorySource(['Pop'])
                ],
                3,
                3
            ),
            new SequencePosition(
                TracklistGeneratorType::average,
                [
                    new DirectorySource(['Pop'])
                ],
                3,
                5
            ),
            new SequencePosition(
                TracklistGeneratorType::new_or_long_standing,
                [
                    new DirectorySource(['Pop'])
                ],
                3,
                5
            )
        ]);

        $this->assertCount(10, $tracks_list);
    }

    #[Test]
    public function attemptGenerateWithSmartGeneratorType(): void
    {
        $this->average_estimate_tracklist_generator
            ->expects($this->once())
            ->method('build')
            ->willReturnCallback(function (array $dirs, int $min_count, int $max_count) {
            $this->assertContains('Pop', $dirs);
            $this->assertCount(1, $dirs);
            $this->assertEquals(3, $min_count);
            $this->assertEquals(5, $max_count);

            return ['Pop/four.mp3', 'Pop/five.mp3', 'Pop/six.mp3'];
            });
        $this->best_estimate_tracklist_generator
            ->expects($this->once())
            ->method('build')
            ->willReturnCallback(function (array $dirs, int $count, array $exclude_paths) {
                $this->assertContains('Pop', $dirs);
                $this->assertCount(1, $dirs);
                $this->assertEquals(3, $count);

                $this->assertEquals(['Pop/four.mp3', 'Pop/five.mp3', 'Pop/six.mp3'], $exclude_paths);

                return ['Pop/one.mp3', 'Pop/two.mp3', 'Pop/three.mp3'];
            });
        $this->new_or_long_standing_tracklist_generator
            ->expects($this->once())
            ->method('build')
            ->willReturnCallback(function (
                array $dirs,
                int $min_count,
                int $max_count,
                array $exclude_paths
            ) {
                $this->assertContains('Pop', $dirs);
                $this->assertCount(1, $dirs);
                $this->assertEquals(3, $min_count);
                $this->assertEquals(5, $max_count);

                $this->assertEquals(
                    array_merge(
                        ['Pop/four.mp3', 'Pop/five.mp3', 'Pop/six.mp3'],
                        ['Pop/one.mp3', 'Pop/two.mp3', 'Pop/three.mp3']
                    ),
                     $exclude_paths
                );

                return ['Pop/seven.mp3', 'Pop/eight.mp3', 'Pop/nine.mp3'];
            });

        $tracks_list = $this->sequence_generator->generate([
            new SequencePosition(
                TracklistGeneratorType::smart,
                [
                    new DirectorySource(['Pop'])
                ],
                3,
                5
            )
        ]);

        $this->assertCount(9, $tracks_list);
    }
}
