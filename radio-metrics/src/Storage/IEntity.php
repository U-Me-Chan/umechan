<?php

namespace Ridouchire\RadioMetrics\Storage;

interface IEntity
{
    public static function fromArray(array $state): self;
    public function toArray(): array;
}
