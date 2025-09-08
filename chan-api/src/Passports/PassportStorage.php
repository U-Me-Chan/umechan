<?php

namespace PK\Passports;

use Medoo\Medoo;
use OutOfBoundsException;
use PK\Passports\Passport;

class PassportStorage
{
    public function __construct(
        private Medoo $db
    ) {
    }

    public function fetch(): array
    {
        $passport_datas = $this->db->select('passports', '*', ['ORDER' => ['name' => 'ASC']]);

        if (!$passport_datas) {
            return [[], 0];
        }

        $count = $this->db->count('passports');

        $passports = array_map(function (array $passport_data) {
            return Passport::fromArray($passport_data);
        }, $passport_datas);

        return [$passports, $count];
    }

    public function findOne(array $filters = []): Passport
    {
        $conditions = [];

        if (isset($filters['hash'])) {
            $conditions['hash'] = $filters['hash'];
        }

        if (empty($conditions)) {
            throw new \InvalidArgumentException();
        }

        $passport_data = $this->db->get('passports', '*', $conditions);

        if (!$passport_data) {
            throw new OutOfBoundsException();
        }

        return Passport::fromArray($passport_data);
    }

    public function save(Passport $passport): void
    {
        try {
            $this->db->insert('passports', $passport->toArray());
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new \RuntimeException("Нельзя использовать такое имя или пароль");
            }

            throw new \RuntimeException("Произошла ошибка, обратитесь к администратору");
        }
    }
}
