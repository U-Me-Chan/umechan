<?php

namespace PK\Shared\Infrastructrure;

interface IQueryHandler
{
    public function execute(IQuery $query): IQueryResponse;
}
