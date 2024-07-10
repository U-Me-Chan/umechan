<?php

namespace PK\Shared\Infrastructrure;

interface IQuery
{
    public function toQueryCriteria(): IQueryCriteria;
}
