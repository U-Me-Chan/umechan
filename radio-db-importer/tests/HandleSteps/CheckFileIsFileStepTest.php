<?php

use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioDbImporter\HandlerData;
use Ridouchire\RadioDbImporter\HandleSteps\CheckFileIsFileStep;

class CheckFileIsFileStepTest extends TestCase
{
    private CheckFileIsFileStep $check_file_is_file_step;

    public function setUp(): void
    {
        $this->check_file_is_file_step = new CheckFileIsFileStep($this->createMock(Logger::class));
    }

    #[Test]
    public function attemptFileIsNotValid(): void
    {
        $this->check_file_is_file_step->process(new HandlerData(
            new SplFileInfo('/tmp/1.mp3')
        ))->catch(function ($error) {
            $this->assertInstanceOf(RuntimeException::class, $error);
        });
    }

    #[Test]
    public function attemptFileIsValid(): void
    {
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();
        $file->method('isFile')->willReturn(true);

        $this->check_file_is_file_step->process(new HandlerData($file))
            ->then(function (HandlerData $data) {
                $this->assertInstanceOf(HandlerData::class, $data);
            });
    }
}
