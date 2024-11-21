<?php

namespace PK\Shared\Infrastructrure\QueryCriteries\QueryMedooCriteria;

enum Operator: string
{
    case EQUAL = '';
    case GREATER_THAN = '[>]';
    case GREATER_THAN_OR_EQUAL = '[>=]';
    case NOT_EQUAL = '[!]';
    case BETWEEN = '[<>]';
    case NOT_BETWEEN = '[><]';
    case LIKE = '[~]';
    case NOT_LIKE = '[!~]';
}
