<?php

namespace PK\Services;

use PK\Utils\ApplicationHook;

final class HookService
{
    private array $hooks = [];

    public function __construct()
    {
        $this->hooks = [
            ApplicationHook::before_run->name  => [],
            ApplicationHook::after_run->name   => [],
            ApplicationHook::before_send->name => [],
            ApplicationHook::after_send->name  => []
        ];
    }

    public function registerHookHandler(ApplicationHook $hook, callable $handler): void
    {
        $this->hooks[$hook->name][] = $handler;
    }

    public function setHook(ApplicationHook $hook, array $context = []): void
    {
        foreach ($this->hooks[$hook->name] as $handler) {
            $handler($context);
        }
    }
}
