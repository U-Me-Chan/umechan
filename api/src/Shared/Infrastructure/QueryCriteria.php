<?php

namespace PK\Shared\Infrastructrure;

use PK\Shared\Infrastructrure\IQueryCriteria;

class QueryCriteria implements IQueryCriteria
{
    public function __construct(
        public readonly ?QueryCriteriaFilters $filters = null,
        public readonly ?QueryCriteriaOrdering $ordering = null,
        public readonly ?QueryCriteriaLimiting $limiting = null
    ) {
    }

    public function getFilters(): array
    {
        return [];
    }

    public function getOrderind(): array
    {
        return [];
    }

    public function getLimiting(): array
    {
        return [];
    }
}
