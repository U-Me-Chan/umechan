<?php

namespace PK\Infrastructure;

use PK\Domain\Passport;
use PK\Infrastructure\IRepository;

interface IPassportRepository extends IRepository
{
    public function save(Passport $passport): void;
}
