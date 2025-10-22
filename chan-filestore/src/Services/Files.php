<?php

namespace IH\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile as ReceivedFile;
use IH\Enums\Mimetype;
use IH\Exceptions\FileNotUploaded;
use IH\Exceptions\FileUnsupportedMimetype;
use IH\File;
use IH\FileCollection;
use IH\FileRepository;
use IH\Services\MimetypeExtractors\FinfoMimetypeExtractor;

class Files
{
    public function __construct(
        private FinfoMimetypeExtractor $finfo_mimetype_extractor,
        private ThumbnailCreator $thumbnail_creator,
        private FileRepository $file_repository,
        private string $static_url,
        private string $files_dir
    ) {
    }

    public function getFileList(int $offset = 0, int $limit = 20): FileCollection
    {
        return $this->file_repository->findMany(['offset' => $offset, 'limit' => $limit]);
    }

    public function uploadFile(ReceivedFile $file): File
    {
        $mimetype = $this->finfo_mimetype_extractor->extract($file->getRealPath());

        if ($mimetype == Mimetype::unsupported) {
            throw new FileUnsupportedMimetype();
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $name      = uniqid();

        $filename = "{$name}.{$extension}";

        try {
            $file->move($this->files_dir, $filename);
        } catch (FileException) {
            throw new FileNotUploaded();
        }

        list($filename, $thumbname) = $this->thumbnail_creator->execute($mimetype, $filename);

        return new File(
            $filename,
            $this->static_url . '/' . $filename,
            $this->static_url . '/' . $thumbname
        );
    }

    public function deleteFile(string $filename): void
    {
        $filepath  = $this->files_dir . $filename;
        $thumbpath = $this->files_dir . 'thumb.' . $filename;

        if (is_file($filepath)) {
            unlink($filepath);
        }

        if (is_file($thumbpath)) {
            unlink($thumbpath);
        }
    }
}
