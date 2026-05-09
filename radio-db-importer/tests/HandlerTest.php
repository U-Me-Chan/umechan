<?php

use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\Promise;
use Ridouchire\RadioDbImporter\DirectoryIterator;
use Ridouchire\RadioDbImporter\Exceptions\DirectoryIsEndException;
use Ridouchire\RadioDbImporter\Handler;
use Ridouchire\RadioDbImporter\HandlerData;
use Ridouchire\RadioDbImporter\HandlerStepsChain;
use Ridouchire\RadioDbImporter\HandleStep;

class HandlerTest extends TestCase
{
    private MockObject|Logger $logger;
    private MockObject|DirectoryIterator $directory_iterator;

    public function setUp(): void
    {
        $this->logger             = $this->createMock(Logger::class);
        $this->directory_iterator = $this->createMock(DirectoryIterator::class);
    }

    #[Test]
    public function attemptRunWhenDirectoryIsEnd(): void
    {
        $this->directory_iterator->expects($this->once())
            ->method('getFile')
            ->willThrowException(new RuntimeException());

        $this->logger->expects($this->once())->method('info');

        $pipeline = new HandlerStepsChain();

        $this->expectException(DirectoryIsEndException::class);

        $handler = new Handler(
            $this->logger,
            $this->directory_iterator,
            $pipeline
        );

        $handler->__invoke();
    }

    #[Test]
    public function attemptRunWhenPipelineIsFailed(): void
    {
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();

        $this->directory_iterator->expects($this->once())
            ->method('getFile')
            ->willReturn($file);
        $this->directory_iterator->expects($this->once())
            ->method('next');

        $this->logger->expects($this->once())
            ->method('error');

        $pipeline = new HandlerStepsChain();

        $handler_step = $this->createMock(HandleStep::class);
        $handler_step->method('process')
            ->willReturn(new Promise(function ($resolve, $reject) {
                $reject(new RuntimeException());
            }));

        $pipeline->addHandler($handler_step);

        $handler = new Handler(
            $this->logger,
            $this->directory_iterator,
            $pipeline
        );

        $handler->__invoke();
    }

    #[Test]
    public function attemptRunWhenPipelineIsSuccess(): void
    {
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();

        $this->directory_iterator->expects($this->once())
            ->method('getFile')
            ->willReturn($file);

        $pipeline = new HandlerStepsChain();

        $data = new HandlerData($file);

        $handler_step = $this->createMock(HandleStep::class);
        $handler_step->method('process')
            ->willReturn(new Promise(function ($resolve) use ($data) {
                $this->assertInstanceOf(SplFileInfo::class, $data->file);

                $resolve($data);
            }));

        $pipeline->addHandler($handler_step);

        $handler = new Handler(
            $this->logger,
            $this->directory_iterator,
            $pipeline
        );

        $handler->__invoke();
    }
}
