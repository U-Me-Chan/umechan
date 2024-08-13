<?php

use PHPUnit\Framework\TestCase;
use Ridouchire\RadioMetrics\Storage\Entites\Track;

class TrackTest extends TestCase
{
    public function testIncreaseEstimate(): void
    {
        $track = Track::draft('foo', 'bar', 'hash', './', 120);

        $track->increaseEstimate(2);

        $this->assertEquals(2, $track->getEstimate());
    }

    /**
     * @dataProvider dataProviderForTestDecreaseEstimate
     */
    public function testDecreaseEstimate(int $value, int $estimate): void
    {
        $track = Track::draft('foo', 'bar', 'hash', './', 120);

        $track->increaseEstimate($value);
        $track->decreaseEstimate();

        $this->assertEquals($estimate, $track->getEstimate());
    }

    public function dataProviderForTestDecreaseEstimate(): array
    {
        return [
            [240, -120],
            [0, -360],
            [120, -240],
            [-120, -240],
            [360, 180],
            [-240, -480]
        ];
    }
}
