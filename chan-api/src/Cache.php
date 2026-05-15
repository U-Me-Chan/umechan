<?php

namespace PK;

interface Cache
{
    public function get(string $key, mixed $default_value = null): mixed;
    public function set(string $key, mixed $value, int $expiration_time = 0): void;
    public function delete(string $key): void;
}
