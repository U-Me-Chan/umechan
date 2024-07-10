<?php

namespace PK\Application\QueryResponses;

use PK\Application\IQueryResponse;

class Collection implements IQueryResponse
{
    public function __construct(
        public readonly string $names,
        public readonly array $entities,
        public readonly int $count
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            $this->names => $this->entities,
            'count'      => $this->count
        ];
    }
}
