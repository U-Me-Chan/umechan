<?php

namespace PK\Passports;

use JsonSerializable;
use OpenApi\Attributes as OA;
use PK\Passports\Passport\Name;
use PK\Passports\Passport\Password;

#[OA\Schema]
class Passport implements JsonSerializable
{
    public static function draft(string $name, string $password): self
    {
        return new self(Name::draft($name), Password::draft($password));
    }

    public static function fromArray(array $state): self
    {
        return new self(Name::fromString($state['name']), Password::fromString($state['hash']));
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
        #[OA\Property(description: 'Видимое имя')]
        public Name $name,
        public Password $hash
    ) {
    }
}
