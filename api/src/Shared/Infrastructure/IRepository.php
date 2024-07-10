<?php

namespace PK\Shared\Infrastructrure;

interface IRepository
{
    public function findMany(array $filters = [], array $ordering = []);
    public function findOne(array $filters = []);
}
