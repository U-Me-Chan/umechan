<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PK\Events\ChanEvent;
use PK\Events\ChanEventBuilder;
use PK\Events\ChanEventPayload;
use PK\Events\Message;

final class ChanEventBuilderTest extends TestCase
{
    private ChanEventBuilder $builder;

    public function setUp(): void
    {
        $this->builder = new ChanEventBuilder('foo');
    }

    #[Test]
    public function attemptBuildChanEvent(): void
    {
        /** @var MockObject|ChanEventPayload */
        $payload = $this->createMock(ChanEventPayload::class); // @phpstan-ignore varTag.nativeType
        $payload->method('toArray')->willReturn(['bar' => 'spam']);

        $event = $this->builder->build(ChanEvent::class, $payload);

        $this->assertInstanceOf(ChanEvent::class, $event);
        $this->assertInstanceOf(Message::class, $event);
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'nodeSign'  => 'foo',
                    'eventName' => (new ReflectionClass(ChanEvent::class))->getShortName(),
                    'payload'   => [
                        'bar' => 'spam'
                    ]
                ]
            ),
            $event->json
        );
    }
}
