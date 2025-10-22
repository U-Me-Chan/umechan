<?php

use IH\File;
use IH\FileCollection;
use IH\FileRepository;
use IH\Utils\DirectoryIterator;
use Medoo\Medoo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileRepositoryTest extends TestCase
{
    private const STATIC_URL = 'https://domain.tld/files';

    private MockObject|Medoo $db;
    private MockObject|DirectoryIterator $directory_iterator;
    private FileRepository $file_repository;

    public function setUp(): void
    {
        /** @var MockObject|Medoo */
        $this->db = $this->createMock(Medoo::class);

        /** @var MockObject|DirectoryIterator */
        $this->directory_iterator = $this->createMock(DirectoryIterator::class);

        $this->file_repository = new FileRepository(
            $this->db,
            $this->directory_iterator,
            self::STATIC_URL
        );
    }

    public function testFindMany(): void
    {
        $this->directory_iterator->method('count')->willReturn(3);
        $this->directory_iterator->method('getSlice')->willReturnCallback(function (int $offset, int $limit) {
            $this->assertEquals(0, $offset);
            $this->assertEquals(20, $limit);

            $file_jpeg = $this->createMock(SplFileInfo::class);
            $file_jpeg->method('getBasename')->willReturn('1q2w3e4r.jpeg');

            $file_webm = $this->createMock(SplFileInfo::class);
            $file_webm->method('getBasename')->willReturn('1q2w3e4r.webm');

            $file_mp4 = $this->createMock(SplFileInfo::class);
            $file_mp4->method('getBasename')->willReturn('1q2w3e4r.mp4');

            return [$file_jpeg, $file_webm, $file_mp4];
        });

        $this->db->method('select')->willReturn([1], [2], [3]);

        $collection = $this->file_repository->findMany(['limit' => 20, 'offset' => 0]);

        $this->assertInstanceOf(FileCollection::class, $collection);
        $this->assertEquals(3, $collection->count);
        $this->assertCount(3, $collection->files);

        /** @var File $file */
        foreach ($collection->files as $k => $file) {
            if ($k == 0) {
                $this->assertInstanceOf(File::class, $file);
                $this->assertEquals('1q2w3e4r.jpeg', $file->name);
                $this->assertEquals(self::STATIC_URL . '/' . $file->name, $file->original);
                $this->assertEquals(self::STATIC_URL . '/' . 'thumb.' . $file->name, $file->thumbnail);
                $this->assertEquals([1], $file->post_ids);
            } else if ($k == 1) {
                $this->assertInstanceOf(File::class, $file);
                $this->assertEquals('1q2w3e4r.webm', $file->name);
                $this->assertEquals(self::STATIC_URL . '/' . $file->name, $file->original);
                $this->assertEquals(self::STATIC_URL . '/' . 'thumb.' . $file->name . '.jpeg', $file->thumbnail);
                $this->assertEquals([2], $file->post_ids);
            } else if ($k == 2) {
                $this->assertInstanceOf(File::class, $file);
                $this->assertEquals('1q2w3e4r.mp4', $file->name);
                $this->assertEquals(self::STATIC_URL . '/' . $file->name, $file->original);
                $this->assertEquals(self::STATIC_URL . '/' . 'thumb.' . $file->name . '.jpeg', $file->thumbnail);
                $this->assertEquals([3], $file->post_ids);
            }
        }
    }
}
