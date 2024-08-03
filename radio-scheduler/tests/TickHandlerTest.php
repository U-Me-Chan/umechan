<?php

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\ByEstimateInGenre;
use Ridouchire\RadioScheduler\RotationStrategies\GenrePattern;
use Ridouchire\RadioScheduler\RotationStrategies\RandomTracksInGenrePattern;
use Ridouchire\RadioScheduler\TickHandler;
use Ridouchire\RadioScheduler\Utils\TickCounter;

class TickHandlerTest extends TestCase
{
    private RotationMaster $rotation_master;
    private TickHandler $tick_handler;

    public function setUp(): void
    {
        /** @var Mpd|MockObject */
        $mpd = $this->createMock(Mpd::class);

        /** @var ByEstimateInGenre|MockObject */
        $by_estimate_in_genre_strategy = $this->createMock(ByEstimateInGenre::class);

        /** @var RandomTracksInGenrePattern|MockObject */
        $random_tracks_in_genre_pattern = $this->createMock(RandomTracksInGenrePattern::class);

        $this->rotation_master = new RotationMaster($this->createMock(Logger::class));
        $this->rotation_master->addStrategy($random_tracks_in_genre_pattern);
        $this->rotation_master->addStrategy($by_estimate_in_genre_strategy);

        $this->tick_handler = new TickHandler($this->rotation_master, $mpd);
    }

    public function test(): void
    {
        TickCounter::create(0);

        $c = $this->tick_handler;
        $c();

        $this->assertContainsEquals($this->rotation_master->getCurrentStrategy(), [RandomTracksInGenrePattern::NAME, ByEstimateInGenre::NAME]);

        $strategy = $this->rotation_master->getCurrentStrategy();

        TickCounter::tick();

        $c();

        $this->assertEquals($strategy, $this->rotation_master->getCurrentStrategy());

        TickCounter::create(60 * 45);

        $c();

        $this->assertNotEquals($strategy, $this->rotation_master->getCurrentStrategy());
    }
}
