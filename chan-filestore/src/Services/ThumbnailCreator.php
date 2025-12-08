<?php

namespace IH\Services;

use IH\Enums\Mimetype;
use IH\Exceptions\ThumbnailerNotFoundByMimetype;
use IH\Services\Thumbnailer;

class ThumbnailCreator
{
    private array $map = [];

    public function register(Thumbnailer $thumbnailer): void
    {
        $this->map[$thumbnailer::getType()->value] = $thumbnailer;
    }

    /**
     * Запускает изготовить миниатюры согласно типу файла, возвращает имя файла и его миниатюры
     */
    public function execute(Mimetype $mimetype, string $filename): array
    {
        if (!isset($this->map[$mimetype->value])) {
            throw new ThumbnailerNotFoundByMimetype();
        }

        /** @var Thumbnailer */
        $thumbnailer = $this->map[$mimetype->value];

        $thumbnailer->readFromFile($filename);
        $thumbnailer->create(240, 320);
        $thumbnail_name = $thumbnailer->save($filename);

        return [$filename, $thumbnail_name];
    }
}
