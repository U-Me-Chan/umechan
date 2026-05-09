<?php

use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioDbImporter\HandlerData;
use Ridouchire\RadioDbImporter\HandleSteps\CheckFileExtenstionStep;

class CheckFileExtensionStepTest extends TestCase
{
    private CheckFileExtenstionStep $check_file_extenstion_step;

    public function setUp(): void
    {
        $this->check_file_extenstion_step = new CheckFileExtenstionStep($this->createMock(Logger::class));
    }

    #[Test]
    public function attemptFileWithValidExtension(): void
    {
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();
        $file->method('getExtension')->willReturn('mp3');

        $this->check_file_extenstion_step->process(new HandlerData($file))
            ->then(function ($data) {
                $this->assertInstanceOf(HandlerData::class, $data);
            });
    }


    #[Test]
    public function attemptFileWithNotValidExtension(): void
    {
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();
        $file->method('getExtension')->willReturn('ogg');

        $this->check_file_extenstion_step->process(new HandlerData($file))
            ->catch(function ($error) {
                $this->assertInstanceOf(RuntimeException::class, $error);
            });
    }
}
