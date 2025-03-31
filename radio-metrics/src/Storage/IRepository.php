<?php

namespace Ridouchire\RadioMetrics\Storage;

use Ridouchire\RadioMetrics\Exceptions\EntityNotFound;

interface IRepository
{
    /**
     * Выполняет поиск сущностей по заданным фильтрам, возвращает список сущностей и их количество
     *
     * @param array $filters          Фильтры в виде ассоциативного массива
     * @param int   $filters[$offset] Смещение относительно первой сущности в списке
     * @param int   $filters[$limit]  Количество сущностей в ответе
     *
     * @return array
     *
     * @phpstan-ignore parameter.phpDocType
     */
    public function findMany(array $filters = []): array;

    /**
     * Выполняет поиск сущности по заданным фильтрам
     *
     * @param array $filters Фильтры в виде ассоциативного массива
     *
     * @throws EntityNotFound Если сущность не найдена
     *
     * @return AEntity
     */
    public function findOne(array $filters = []): AEntity;

    /**
     * Сохраняет состояние сущности, возвращает её идентификатор
     *
     * @param AEntity $entity Сущность
     *
     * @return int
     */
    public function save($entity): int;

    /**
     * Удаляет сущность
     *
     * @param AEntity $entity Сущность
     *
     * @return bool
     */
    public function delete($entity): bool;
}
