<?php

namespace PK\Shared\Infrastructrure;

interface IQueryCriteria
{
    public function getFilters(): array;
    public function getOrderind(): array;
    public function getLimiting(): array;
}
