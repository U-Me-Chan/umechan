<?php

namespace IH\Services;

use IH\Services\Thumbnailer;
use IH\Services\FileUploader;

class ThumbnailCreator
{
    private array $map = [];

    public function register(Thumbnailer $thumbnailer): void
    {
        $this->map[$thumbnailer::getType()->value] = $thumbnailer;
    }

    /**
     * Запускает изготовить миниатюры согласно типу файла, возвращает имя файла и его миниатюры
     *
     * @param FileUploader $uploaded_file
     *
     * @return array
     */
    public function execute(FileUploader $uploaded_file): array
    {
        if (!isset($this->map[$uploaded_file->getMimetype()->value])) {
            throw new \Exception;
        }

        /** @var Thumbnailer */
        $thumbnailer = $this->map[$uploaded_file->getMimetype()->value];

        $thumbnailer->readFromFile($uploaded_file->getFilepath());
        $thumbnailer->create(240, 320);
        $thumbnail_name = $thumbnailer->save($uploaded_file->getFilename());

        return [$uploaded_file->getFilename(), $thumbnail_name];
    }
}
