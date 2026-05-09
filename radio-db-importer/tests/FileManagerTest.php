<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioDbImporter\FileManager;

class FileManagerTest extends TestCase
{
    private FileManager $file_manager;
    private string $tmp_dir;

    public function setUp(): void
    {
        $this->tmp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'music_dir';

        $this->file_manager = new FileManager(
            '/convert',
            '/notags',
            $this->tmp_dir . DIRECTORY_SEPARATOR . 'BadEstimate',
            $this->tmp_dir
        );
    }

    #[Test]
    #[TestWith(['/tmp/music_dir/Pop/1.mp3', 'Pop/1.mp3'])]
    #[TestWith(['/tmp/music_dir/Pop', 'Pop'])]
    #[TestWith(['/convert/1.mp3', '/convert/1.mp3'])]
    public function getRelativeFilepath(string $absolute_path, string $expected_path): void
    {
        $this->assertEquals($expected_path, $this->file_manager->getRelativeFilepath($absolute_path));
    }

    #[Test]
    #[TestWith(['Pop/1.mp3', '/tmp/music_dir/Pop/1.mp3'])]
    public function getAbsolutePath(string $relative_path, string $expected_path): void
    {
        $this->assertEquals($expected_path, $this->file_manager->getAbsoluteFilepath($relative_path));
    }

    #[Test]
    #[TestWith(['Pop/1.mp3', false])]
    #[TestWith(['BadEstimate/1.mp3', true])]
    #[TestWith(['/tmp/music_dir/BadEstimate/1.mp3', true])]
    public function isNegativeDir(string $path, bool $expected): void
    {
        $this->assertEquals($expected, $this->file_manager->isNegativeDir($path));
    }

    #[Test]
    #[TestWith(['Pop/1.mp3', false])]
    #[TestWith(['Duplicate/1.mp3', true])]
    #[TestWith(['/tmp/music_dir/Duplicate/1.mp3', true])]
    public function isDuplicateDir(string $path, bool $expected): void
    {
        $this->assertEquals($expected, $this->file_manager->isDuplicateDir($path));
    }
}
