<?php

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\RotationMaster;
use Ridouchire\RadioScheduler\RotationStrategies\GenrePattern;
use Ridouchire\RadioScheduler\RotationStrategies\NewInGenre;
use Ridouchire\RadioScheduler\RotationStrategies\TopInGenre;
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

        /** @var GenrePattern|MockObject */
        $genre_pattern_strategy = $this->createMock(GenrePattern::class);

        /** @var TopInGenre|MockObject */
        $top_in_genre_strategy = $this->createMock(TopInGenre::class);

        /** @var NewInGenre|MockObject */
        $new_in_genre_strategy = $this->createMock(NewInGenre::class);

        $this->rotation_master = new RotationMaster($this->createMock(Logger::class));
        $this->rotation_master->addStrategy($genre_pattern_strategy);
        $this->rotation_master->addStrategy($top_in_genre_strategy);
        $this->rotation_master->addStrategy($new_in_genre_strategy);

        $this->tick_handler = new TickHandler($this->rotation_master, $mpd);
    }

    public function test(): void
    {
        TickCounter::create(0);

        $c = $this->tick_handler;
        $c();

        $this->assertContainsEquals($this->rotation_master->getCurrentStrategy(), [TopInGenre::NAME, GenrePattern::NAME, NewInGenre::NAME]);

        $strategy = $this->rotation_master->getCurrentStrategy();

        TickCounter::tick();

        $c();

        $this->assertEquals($strategy, $this->rotation_master->getCurrentStrategy());

        TickCounter::create(60 * 31);

        $c();

        $this->assertNotEquals($strategy, $this->rotation_master->getCurrentStrategy());
    }
}
