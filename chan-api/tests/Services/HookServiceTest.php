<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PK\Http\Request;
use PK\Http\Response;
use PK\Http\Responses\JsonResponse;
use PK\Services\HookService;
use PK\Utils\ApplicationHook;

final class HookServiceTest extends TestCase
{
    private HookService $hook_service;

    public function setUp(): void
    {
        $this->hook_service = new HookService();
    }

    #[Test]
    public function runApplicationHookBeforeRun(): void
    {
        $this->hook_service->registerHookHandler(ApplicationHook::before_run, function ($context) {
            list($request) = $context;

            $this->assertInstanceOf(Request::class, $request);
        });

        $this->hook_service->setHook(ApplicationHook::before_run, [new Request()]);
    }

    #[Test]
    public function runApplicationHooksAfterRun(): void
    {
        $this->hook_service->registerHookHandler(ApplicationHook::after_run, function ($context) {
            list($req, $res) = $context;

            $this->assertInstanceOf(Request::class, $req);
            $this->assertInstanceOf(Response::class, $res);
        });

        $this->hook_service->setHook(ApplicationHook::after_run, [new Request, new JsonResponse()]);
    }

    #[Test]
    public function runApplicationHooksBeforeSend(): void
    {
        $this->hook_service->registerHookHandler(ApplicationHook::before_send, function ($context) {
            list($res) = $context;

            $this->assertInstanceOf(Response::class, $res);
        });
        $this->hook_service->setHook(ApplicationHook::before_send, [new JsonResponse()]);
    }

    #[Test]
    public function runApplicationHooksAfterSend(): void
    {
        $this->hook_service->registerHookHandler(ApplicationHook::after_send, function ($context) {
            $this->assertEmpty($context);
        });

        $this->hook_service->setHook(ApplicationHook::after_send);
    }
}
