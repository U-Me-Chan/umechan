<?php

namespace PK\Passports\Passport;

use JsonSerializable;
use PK\Passports\Passport\Name;
use PK\Passports\Passport\Password;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'Passport')]
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
        #[OA\Property(type: 'string', description: 'Зарегистрированное имя')]
        public Name $name,
        public Password $hash
    ) {
    }
}
