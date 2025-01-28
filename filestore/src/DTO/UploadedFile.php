<?php

namespace IH\DTO;

use IH\DTO\DTO;

final class UploadedFile implements DTO
{
    public function __construct(
        private string $original_file_url,
        private string $thumbnail_file_url
    ) {
    }

    public function toArray(): array
    {
        return [
            'original_file'  => $this->original_file_url,
            'thumbnail_file' => $this->thumbnail_file_url
        ];
    }
}
