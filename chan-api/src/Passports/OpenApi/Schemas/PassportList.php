<?php

namespace PK\Passports\OpenApi\Schemas;

use OpenApi\Attributes as OA;
use PK\Passports\Passport;

#[OA\Schema]
abstract class PassportList
{
    /**
     * @var list<Passport> $passports
     */
    #[OA\Property(
        description: 'Список зарегистрированных имён',
        items: new OA\Items(type: 'string')
    )]
    public array $passports;

    #[OA\Property]
    public int $count;
}
