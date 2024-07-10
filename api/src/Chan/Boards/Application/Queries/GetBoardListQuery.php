<?php

namespace PK\Application\Queries;

use PK\Shared\Infrastructrure\IQuery;
use PK\Shared\Infrastructrure\IQueryCriteria;
use PK\Shared\Infrastructrure\QueryCriteries\QueryMedooCriteria;
use PK\Shared\Infrastructrure\QueryCriteries\QueryMedooCriteria\Condition;
use PK\Shared\Infrastructrure\QueryCriteries\QueryMedooCriteria\Operator;
use PK\Shared\Infrastructrure\QueryCriteries\QueryMedooCriteria\OrderDirection;

class GetBoardListByTags implements IQuery
{
    public function __construct(
        public readonly array $filters = [],
        public readonly array $ordering = [
            'tag' => OrderDirection::ASCENDING->value
        ]
    ) {
    }

    public function toQueryCriteria(): IQueryCriteria
    {
        $criteria = new QueryMedooCriteria();

        if (!empty($this->tags)) {
            $criteria->addFilter(Condition::AND, 'tag', Operator::EQUAL, $this->tags);
        }

        $criteria->addLimit(999);

        foreach ($this->ordering as $field => $direction) {
            $criteria->addOrder($field, $direction);
        }

        return $criteria;
    }
}
