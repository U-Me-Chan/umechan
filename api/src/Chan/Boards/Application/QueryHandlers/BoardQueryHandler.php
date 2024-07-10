<?php

namespace PK\Application\QueryHandlers;

use PK\Application\IQuery;
use PK\Application\IQueryHandler;
use PK\Application\IQueryResponse;
use PK\Application\QueryResponses\Collection;
use PK\Chan\Boards\Infrastructure\IBoardRepository;

class BoardQueryHandler implements IQueryHandler
{
    public function __construct(
        private IBoardRepository $board_repo
    ) {
    }

    public function execute(IQuery $query): IQueryResponse
    {
        $criteria = $query->toQueryCriteria();

        list($boards, $count) = $this->board_repo->findMany(array_merge(
            $criteria->getFilters(),
            $criteria->getOrderind(),
            $criteria->getLimiting()
        ));

        return new Collection('boards', $boards, $count);
    }
}
