<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Promise\Promise;
use Ridouchire\RadioDbImporter\HandlerData;
use Ridouchire\RadioDbImporter\HandlerStepsChain;
use Ridouchire\RadioDbImporter\HandleStep;

class HandlerStepsChainTest extends TestCase
{
    private HandlerStepsChain $handler_steps_chain;

    public function setUp(): void
    {
        $this->handler_steps_chain = new HandlerStepsChain();
    }

    #[Test]
    public function attemptRunWithoutHandlers(): void
    {
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();

        $this->handler_steps_chain->process(new HandlerData($file))
            ->then(function (HandlerData $data) {
                $this->assertInstanceOf(HandlerData::class, $data);
                $this->assertInstanceOf(SplFileInfo::class, $data->file);
                $this->assertNull($data->track);
            });
    }

    #[Test]
    public function attemptRunWithRejectHandler(): void
    {
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();

        $handler = $this->createMock(HandleStep::class);
        $handler->method('process')->willReturn(new Promise(function ($resolve, $reject) {
            $reject(new RuntimeException('Foo'));
        }));

        $this->handler_steps_chain->addHandler($handler);

        $this->handler_steps_chain->process(new HandlerData($file))
            ->catch(function (RuntimeException $e) {
                $this->assertEquals('Foo', $e->getMessage());
            });
    }

    #[Test]
    public function attemptRunWithResolvehandler(): void
    {
        $file = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();

        $data = new HandlerData($file);

        $handler = $this->createMock(HandleStep::class);
        $handler->method('process')->willReturn(new Promise(function ($resolve, $reject) use ($data) {
            $resolve($data);
        }));

        $this->handler_steps_chain->addHandler($handler);

        $this->handler_steps_chain->process($data)
            ->then(function ($handler_data) {
                $this->assertInstanceOf(HandlerData::class, $handler_data);
            });
    }
}
