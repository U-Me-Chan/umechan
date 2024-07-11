<?php

namespace PK\Passports;

use PK\Base\IRepository;
use PK\Passports\Passport\Passport;

interface IPassportRepository extends IRepository
{
    public function findOne(array $filters = []): Passport;
    public function save(Passport $passport);
    public function delete(Passport $passport);
}
