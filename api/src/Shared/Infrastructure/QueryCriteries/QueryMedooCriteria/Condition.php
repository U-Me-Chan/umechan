<?php

namespace PK\Shared\Infrastructrure\QueryCriteries\QueryMedooCriteria;

enum Condition: string
{
    case AND = 'AND';
    case OR  = 'OR';
}
