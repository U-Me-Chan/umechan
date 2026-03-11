<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PK\Events\ChanEvent;
use PK\Events\ChanEventBuilder;
use PK\Events\MessageBroker;
use PK\Passports\Events\ChanEvents\CreatePassport;
use PK\Passports\Exceptions\NameOrKeyIsForbiddenException;
use PK\Passports\Passport;
use PK\Passports\PassportStorage;
use PK\Passports\Services\PassportService;

final class PassportServiceTest extends TestCase
{
    private const DEFAULT_NAME = 'Anonymous';

    private MockObject|PassportStorage $passport_storage;
    private MockObject|MessageBroker $message_broker;
    private PassportService $passport_service;

    public function setUp(): void
    {
        /**
         * @var MockObject|PassportStorage
         *
         * @phpstan-ignore varTag.noVariable
         */
        $this->passport_storage = $this->createMock(PassportStorage::class);

        /**
         * @var MockObject|MessageBroker
         *
         * @phpstan-ignore varTag.noVariable
         */
        $this->message_broker = $this->createMock(MessageBroker::class);

        $this->passport_service = new PassportService(
            $this->passport_storage,
            $this->message_broker,
            new ChanEventBuilder('foo'),
            self::DEFAULT_NAME
        );
    }

    #[Test]
    public function getList(): void
    {
        $this->passport_storage->expects($this->once())->method('fetch');

        $this->passport_service->getList();
    }

    #[Test]
    public function findByHash(): void
    {
        $this->passport_storage->expects($this->once())->method('findOne')->willReturnCallback(function (array $filters) {
            $this->assertArrayHasKey('hash', $filters);

            return Passport::draft('test', 'test');
        });

        $passport = $this->passport_service->findByHash('hash');

        $this->assertInstanceOf(Passport::class, $passport);
    }

    #[Test]
    public function createPassportWithDefaultName(): void
    {
        $this->expectException(NameOrKeyIsForbiddenException::class);

        $this->passport_service->createPassport(self::DEFAULT_NAME, 'test');
        $this->passport_service->createPassport('test', self::DEFAULT_NAME);
    }

    #[Test]
    public function createPassport(): void
    {
        $this->passport_storage->expects($this->once())->method('save');
        $this->message_broker->expects($this->once())->method('publish')->willReturnCallback(function (ChanEvent $event) {
            $this->assertInstanceOf(CreatePassport::class, $event);

            $this->assertEquals('chan.passports', $event->topic);
            $this->assertJsonStringEqualsJsonString(
                json_encode([
                    'eventName' => 'CreatePassport',
                    'nodeSign'  => 'foo',
                    'payload'   => [
                        'name' => 'test',
                        'hash' => 'ee26b0dd4af7e749aa1a8ee3c10ae9923f618980772e473f8819a5d4940e0db27ac185f8a0e1d5f84f88bc887fd67b143732c304cc5fa9ad8e6f57f50028a8ff'
                    ]
                ]),
                $event->json
            );
        });

        $this->passport_service->createPassport('test', 'test');
    }
}
