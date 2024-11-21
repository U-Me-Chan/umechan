<?php

namespace PK\Application\Queries;

use PK\Application\IQuery;
use PK\Application\IQueryCriteria;
use PK\Application\QueryCriteries\QueryMedooCriteria;

class GetThreadListByBoardIds implements IQuery
{
    public function __construct(
        public readonly array $board_ids,
        public readonly int $offset = 0,
        public readonly int $limit = 10,
        public readonly array $ordering = [
            'updated_at' => 'DESC'
        ]
    ) {
    }

    public function toQueryCriteria(): IQueryCriteria
    {
        return new QueryMedooCriteria(
            [
                'parent_id' => null,
                'board_id'  => $this->board_ids
            ],
            $this->ordering,
            $this->limit,
            $this->offset
        );
    }
}
