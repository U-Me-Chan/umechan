<?php

namespace PK\Events;

use ReflectionClass;
use SensitiveParameter;
use PK\Events\Message;

class ChanEvent extends Message
{
    final public function __construct(
        /** @phpstan-ignore property.onlyWritten */
        #[SensitiveParameter]
        private string $nodeSign,
        public ChanEventPayload $payload,
    ) {
        $this->json = json_encode(
            array_merge(
                [
                    'nodeSign'  => $nodeSign,
                    'eventName' => (new ReflectionClass($this))->getShortName()
                ],
                [
                    'payload' => $payload->toArray()
                ]
            )
        );
    }
}
