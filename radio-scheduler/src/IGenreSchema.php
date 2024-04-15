<?php

namespace Ridouchire\RadioScheduler;

interface IGenreSchema
{
    public static function getAll(): array;
    public static function getRandom(): string;
    public static function getRandomPattern(): array;
}
