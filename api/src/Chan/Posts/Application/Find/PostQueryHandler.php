<?php

namespace PK\Application\QueryHandlers;

use PK\Application\IQuery;
use PK\Application\IQueryHandler;
use PK\Application\QueryResponses\Collection;

class PostQueryHandler implements IQueryHandler
{
    public function __construct(
        private PostRepository $post_repo,
        private BoardRepository $board_repo
    ) {
    }

    public function execute(IQuery $query): Collection
    {
        $criteria = $query->toQueryCriteria();

        list($posts, $count) = $this->post_repo->findMany(array_merge(
            $criteria->getFilters(),
            $criteria->getOrderind(),
            $criteria->getLimiting()
        ));

        return new Collection('posts', $posts, $count);
    }
}
