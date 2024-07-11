<?php

namespace PK\Base;

interface IRepository
{
    public function findMany(array $filters = [], array $ordering = []): array;
    public function findOne(array $filters = []);
}
