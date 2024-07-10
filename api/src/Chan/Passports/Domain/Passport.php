<?php

namespace PK\Domain;

use PK\Domain\PassportName;
use PK\Domain\PassportPassword;
use PK\Shared\Domain\Entity;

class Passport extends Entity
{
    public static function draft(string $name, string $password): self
    {
        return new self(
            PassportName::draft($name),
            PassportPassword::draft($password)
        );
    }

    public static function fromArray(array $state): self
    {
        return new self(
            PassportName::fromString($state['name']),
            PassportPassword::fromString($state['hash'])
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name
        ];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name->toString(),
            'hash' => $this->hash->toString()
        ];
    }

    private function __construct(
        public readonly PassportName $name,
        public readonly PassportPassword $hash
    ) {
    }
}
