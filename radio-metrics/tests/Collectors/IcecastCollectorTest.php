<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Ridouchire\RadioMetrics\Collectors\IcecastCollector;

class IcecastCollectorTest extends TestCase
{
    private Client|MockObject $client;
    private IcecastCollector $icecast_collector;

    public function setUp(): void
    {
        /** @var Client|MockObject */
        $this->client = $this->createMock(Client::class);
        $this->icecast_collector = new IcecastCollector($this->client);
    }

    public function testGetDataWithTwoSource(): void
    {
        $this->client->method('request')->willReturnCallback(function () {
            $res = $this->createMock(Response::class);
            $res->method('getStatusCode')->willReturn(200);
            $res->method('getHeaderLine')->willReturn('Content-type: application/json');

            $stream = $this->createMock(StreamInterface::class);
            $stream->method('__toString')->willReturn(json_encode([
                'icestats' => [
                    'source' => [
                        [
                            'listeners' => 2,
                            'user_agent' => 'MPD'
                        ],
                        [
                            'listeners' => 0,
                        ]
                    ]
                ]
            ]));

            $res->method('getBody')->willReturn($stream);

            return $res;
        });

        $data = $this->icecast_collector->getData();

        $this->assertEquals(2, $data['listeners']);
    }

    public function testGetDataWithOneSource(): void
    {
        $this->client->method('request')->willReturnCallback(function () {
            $res = $this->createMock(Response::class);
            $res->method('getStatusCode')->willReturn(200);
            $res->method('getHeaderLine')->willReturn('Content-type: application/json');

            $stream = $this->createMock(StreamInterface::class);
            $stream->method('__toString')->willReturn(json_encode([
                'icestats' => [
                    'source' => [
                        'listeners' => 3,
                    ]
                ]
            ]));

            $res->method('getBody')->willReturn($stream);

            return $res;
        });

        $data = $this->icecast_collector->getData();

        $this->assertEquals(3, $data['listeners']);
    }
}
