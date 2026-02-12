<?php

namespace PK\Passports\Events\ChanEvents;

use PK\Events\ChanEventPayload;
use PK\Passports\Passport\Password;
use PK\Passports\Passport\Name;
use SensitiveParameter;

class CreatePassportPayload extends ChanEventPayload
{
    public function __construct(
        private Name $name,
        #[SensitiveParameter]
        private Password $password
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name->toString(),
            'hash' => $this->password->toString()
        ];
    }
}
