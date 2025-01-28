<?php

namespace IH\Services;

use IH\Exceptions\FileNotUploaded;
use IH\Exceptions\FileUnsupportedMimetype;
use IH\Enums\Mimetype;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public const UPLOAD_DIR = __DIR__ . '/../../files/';

    private ?string $filename = null;
    private Mimetype $mimetype = Mimetype::unsupported;

    public function __construct(
        private UploadedFile $file
    ) {
        $this->mimetype = $this->extractMimetype();

        if ($this->mimetype == Mimetype::unsupported) {
            throw new FileUnsupportedMimetype();
        }

        $extentions = strtolower($this->file->getClientOriginalExtension());
        $name       = $this->generateId();

        $this->filename   = "{$name}.{$extentions}";

        try {
            $this->file->move(self::UPLOAD_DIR, $this->getFilename());
        } catch (FileException) {
            throw new FileNotUploaded();
        }
    }

    public function getFilepath(): string
    {
        return self::UPLOAD_DIR . $this->getFilename();
    }

    public function getFilename(): string
    {

        return $this->filename;
    }

    public function getMimetype(): Mimetype
    {
        return $this->mimetype;
    }

    private function extractMimetype(): Mimetype
    {
        $file_info = new \finfo(FILEINFO_MIME);

        $mimetype = $file_info->file($this->file->getRealPath());
        $mimetype = explode(';', $mimetype);
        $mimetype = reset($mimetype);

        return match ($mimetype) {
            'image/jpeg' => Mimetype::image,
            'image/png'  => Mimetype::image,
            'image/webp' => Mimetype::image,
            'image/gif'  => Mimetype::image,
            'video/webm' => Mimetype::video,
            'video/mp4'  => Mimetype::video,
            default      => Mimetype::unsupported
        };
    }

    private function generateId(): string
    {
        return uniqid();
    }
}
