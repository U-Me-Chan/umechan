<?php

namespace PK\Passports\Repositories;

use Medoo\Medoo;
use PK\Passports\IPassportRepository;
use PK\Passports\Passport\Passport;

class MedooPassportRepository implements IPassportRepository
{
    public function __construct(
        private Medoo $db
    ) {
    }

    /**
     * Выполняет поиск паспортов
     *
     * @param array  $filters               Список фильтров
     * @param string $filters[$name_substr] Подстрока для поиска по имени
     * @param int    $filters[$limit]       Количество имён в ответе
     * @param int    $filters[$offset]      Смещение относительно первого элемента в списке
     *
     * @return array
     */
    public function findMany(
        array $filters = [],
        array $ordering = [
            'name' => 'ASC'
        ]
    ): array
    {
        $conditions['ORDER'] = $ordering;

        $limiting['LIMIT'] = [
            isset($filters['offset']) ? $filters['offset'] : 0,
            isset($filters['limit']) ? $filters['limit'] : 10
        ];

        $count = $this->db->count('passports', $conditions);

        if ($count == 0) {
            return [[], 0];
        }

        $passport_datas = $this->db->select('passports', '*', array_merge($conditions, $limiting));

        $passports = array_map(fn(array $passport_data) => Passport::fromArray($passport_data), $passport_datas);

        return [$passports, $count];
    }

    public function findOne(array $filters = []): Passport
    {
        $conditions = [];

        if (isset($filters['id'])) {
            $conditions['id'] = $filters['id'];
        }

        if (isset($filters['hash'])) {
            $conditions['hash'] = $filters['hash'];
        }

        if (empty($conditions)) {
            throw new \InvalidArgumentException('Список фильтров пуст');
        }

        $passport_data = $this->db->get('passports', '*', $conditions);

        if (!$passport_data) {
            throw new \OutOfBoundsException('Не найдено такого паспорта');
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

    public function delete(Passport $passport): bool
    {
        return true;
    }
}
