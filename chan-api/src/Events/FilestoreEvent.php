<?php

namespace PK\Events;

use PK\Events\Message;
use ReflectionClass;
use SensitiveParameter;

class FilestoreEvent extends Message
{
    public string $topic = 'chan.filestore';

    final public function __construct(
        #[SensitiveParameter]
        public string $nodeSign,
        public FilestoreEventPayload $payload
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
