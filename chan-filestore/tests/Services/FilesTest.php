<?php

use IH\Enums\Mimetype;
use IH\Exceptions\FileUnsupportedMimetype;
use IH\FileCollection;
use IH\FileRepository;
use IH\Services\Files;
use IH\Services\MimetypeExtractors\FinfoMimetypeExtractor;
use IH\Services\ThumbnailCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FilesTest extends TestCase
{
    private const STATIC_URL = 'https://domain.tld/files';

    private MockObject|FinfoMimetypeExtractor $finfo_mimetype_extractor;
    private MockObject|ThumbnailCreator $thumbnail_creator;
    private MockObject|FileRepository $file_repository;

    private Files $files_service;

    public function setUp(): void
    {
        /** @var MockObject|FinfoMimetypeExtractor */
        $this->finfo_mimetype_extractor = $this->createMock(FinfoMimetypeExtractor::class);

        /** @var MockObject|ThumbnailCreator */
        $this->thumbnail_creator = $this->createMock(ThumbnailCreator::class);

        /** @var MockObject|FileRepository */
        $this->file_repository = $this->createMock(FileRepository::class);

        $this->files_service = new Files(
            $this->finfo_mimetype_extractor,
            $this->thumbnail_creator,
            $this->file_repository,
            self::STATIC_URL
        );
    }

    public function testGetFileList(): void
    {
        $this->file_repository->method('findMany')->willReturn(new FileCollection([], 0));

        $this->assertInstanceOf(FileCollection::class, $this->files_service->getFileList());
    }

    public function testUploadUnsupportedFile(): void
    {
        $this->finfo_mimetype_extractor->method('extract')->willReturn(Mimetype::unsupported);

        /** @var MockObject|UploadedFile */
        $file = $this->createMock(UploadedFile::class);

        $this->expectException(FileUnsupportedMimetype::class);

        $this->files_service->uploadFile($file);
    }
}
