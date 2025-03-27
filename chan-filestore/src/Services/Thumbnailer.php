<?php

namespace IH\Services;

use IH\Enums\Mimetype;

interface Thumbnailer
{
    /**
     * Возвращает тип обрабатываемого файла
     *
     * @return Mimetype
     */
    public static function getType(): Mimetype;

    /**
     * Читает файл по указанному пути
     *
     * @param string $filepath
     *
     * @return void
     */
    public function readFromFile(string $filepath): void;

    /**
     * Создаёт миниатюру с заданными размерами
     *
     * @param int $width
     * @param int $height
     *
     * @return void
     */
    public function create(int $width, int $height): void;

    /**
     * Сохраняет миниатюру и возвращает относительный путь до неё
     *
     * @return string
     */
    public function save(string $filename): string;
}
