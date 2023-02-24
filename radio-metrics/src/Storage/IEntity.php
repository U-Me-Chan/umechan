<?php

namespace Ridouchire\RadioMetrics\Storage;

interface IEntity
{
    public static function fromArray(array $state);
    public function toArray(): array;
}
