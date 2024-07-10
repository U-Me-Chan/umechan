<?php

namespace PK\Shared\Infrastructrure\QueryCriteries;

use PK\Shared\Infrastructrure\IQueryCriteria;;
use PK\Shared\Infrastructrure\QueryCriteries\QueryMedooCriteria\Condition;
use PK\Shared\Infrastructrure\QueryCriteries\QueryMedooCriteria\Operator;
use PK\Shared\Infrastructrure\QueryCriteries\QueryMedooCriteria\OrderDirection;

final class QueryMedooCriteria implements IQueryCriteria
{
    public function __construct(
        private array $filters = [],
        private array $ordering = [],
        private int $limit = 10,
        private int $offset = 0
    ) {
    }

    public function addFilter(
        Condition $condition,
        string $field,
        Operator $operator,
        mixed $value
    ): void
    {
        $this->filters[$condition->value][$field.$operator->value] = $value;
    }

    public function addOrder(
        string $field,
        OrderDirection $direction
    ): void
    {
        $this->ordering[$field] = $direction->value;
    }

    public function addLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function addOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getOrderind(): array
    {
        return [
            'ORDER' => $this->ordering
        ];
    }

    public function getLimiting(): array
    {
        return [
            'LIMIT' => [
                $this->offset,
                $this->limit
            ]
        ];
    }
}
