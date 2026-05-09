<?php

use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioDbImporter\FileManager;
use Ridouchire\RadioDbImporter\HandlerData;
use Ridouchire\RadioDbImporter\HandleSteps\CheckFileDirectoryIsServiceDirStep;

class CheckFileDirectoryIsServiceDirStepTest extends TestCase
{
    private CheckFileDirectoryIsServiceDirStep $check_file_directory_is_service_dir_step;

    public function setUp(): void
    {
        $this->check_file_directory_is_service_dir_step = new CheckFileDirectoryIsServiceDirStep(
            new FileManager(
                '/convert',
                '/tagme',
                '/tmp/BadEstimate',
                '/tmp'
            ),
            $this->createMock(Logger::class)
        );
    }

    #[Test]
    #[TestWith(['/tmp/Pop/1.mp3'])]
    #[TestWith(['/tmp/Bad/1.mp3'])]
    #[TestWith(['/tmp/Foo/1.mp3'])]
    #[TestWith(['/tmp/Bar/1.mp3'])]
    #[TestWith(['/tmp/Spam/Blah/Blah/Blah/1.mp3'])]
    public function atemptFileFromNotServiceDir(string $filepath): void
    {
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();
        $file->method('getPathname')->willReturn($filepath);

        $this->check_file_directory_is_service_dir_step->process(new HandlerData($file))
            ->then(function ($data) {
                $this->assertInstanceOf(HandlerData::class, $data);
            });
    }

    #[Test]
    #[TestWith(['/tmp/BadEstimate/1.mp3'])]
    #[TestWith(['/tmp/Duplicate/1.mp3'])]
    #[TestWith(['/tmp/Duplicate/Pop/Dance/Foo/Bar/1.mp3'])]
    #[TestWith(['/tmp/BadEstimate/Pop/Dance/Foo/Bar/1.mp3'])]
    public function attemptFileFromServiceDir(string $filepath): void
    {
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();
        $file->method('getPathname')->willReturn($filepath);

        $this->check_file_directory_is_service_dir_step->process(new HandlerData($file))
            ->catch(function ($error) {
                $this->assertInstanceOf(RuntimeException::class, $error);
            });
    }
}
