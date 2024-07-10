<?php

namespace PK\Application\Queries;

use PK\Application\IQuery;
use PK\Application\QueryCriteries\QueryMedooCriteria;

class GetThreadReplies implements IQuery
{
    public function __construct(
        public readonly int $thread_id,
        public readonly array $ordering = [
            'id' => 'DESC'
        ],
        public readonly int $limit = 3
    ) {
    }

    public function toQueryCriteria(): QueryMedooCriteria
    {
        return new QueryMedooCriteria(
            [
                'thread_id' => $this->thread_id
            ],
            $this->ordering,
            $this->limit,
            0
        );
    }
}
