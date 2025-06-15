<?php

namespace PK\Posts\Post;

use InvalidArgumentException;

class PasswordHash
{
    private const LENGHT = 5;

    public static function generate(?string $password = null): self
    {
        $password = $password == null ? bin2hex(random_bytes(self::LENGHT)) : $password;

        return new self(
            self::getHashFromString($password),
            $password
        );
    }

    public static function fromString(string $hash): self
    {
        return new self($hash);
    }

    public function isEqualTo(string $password): bool
    {
        return hash_equals($this->hash, self::getHashFromString($password));
    }

    public function toString(): string
    {
        return $this->hash;
    }

    public function clearPasswordToString(): string
    {
        if (!$this->password) {
            throw new InvalidArgumentException();
        }

        return $this->password;
    }

    private static function getHashFromString(string $string): string
    {
        return hash('sha256', $string);
    }

    private function __construct(
        private string $hash,
        private ?string $password = null
    ) {
    }
}
