<?php

namespace IH\Services;

use IH\Enums\Filetype;

interface Thumbnailer
{
    /**
     * Возвращает тип обрабатываемого файла
     */
    public static function getType(): Filetype;

    /**
     * Читает файл по указанному пути
     */
    public function readFromFile(string $filepath): void;

    /**
     * Создаёт миниатюру с заданными размерами
     */
    public function create(int $width, int $height): void;

    /**
     * Сохраняет миниатюру и возвращает относительный путь до неё
     */
    public function save(string $filename): string;
}
