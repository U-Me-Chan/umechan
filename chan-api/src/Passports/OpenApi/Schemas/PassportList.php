<?php

namespace PK\Passports\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema]
abstract class PassportList
{
    #[OA\Property(
        description: 'Список зарегистрированных имён',
        items: new OA\Items(type: 'string')
    )]
    public array $passports;

    #[OA\Property]
    public int $count;
}
